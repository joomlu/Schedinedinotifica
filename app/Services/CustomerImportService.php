<?php

namespace App\Services;

use App\Models\CustomerImportBatch;
use App\Models\CustomerImportRow;
use App\Models\Customers;
use App\Models\GeoCap;
use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use App\Models\RilasciatoDa;
use App\Models\Struttura;
use App\Models\TipoDocumento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerImportService
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_VALID = 'valid';
    public const STATUS_NEEDS_REVIEW = 'needs_review';
    public const STATUS_DUPLICATE_FILE = 'duplicate_file';
    public const STATUS_DUPLICATE_HOTEL = 'duplicate_hotel';
    public const STATUS_DUPLICATE_CHAIN = 'duplicate_chain';
    public const STATUS_IMPORTED = 'imported';

    public function templateHeaders(): array
    {
        return [
            'nome',
            'cognome',
            'tipo_cliente',
            'sesso',
            'email',
            'telefono',
            'cellulare',
            'cap_residenza',
            'comune_residenza',
            'provincia_residenza',
            'indirizzo_residenza',
            'numero_civico_residenza',
            'nazione_residenza',
            'data_nascita',
            'cittadinanza',
            'nazione_nascita',
            'comune_nascita',
            'provincia_nascita',
            'tipo_documento',
            'numero_documento',
            'data_rilascio',
            'data_scadenza',
            'rilasciato_da',
            'nome_gruppo',
            'gruppo',
            'subgroup',
            'subgroup1',
            'note',
        ];
    }

    public function templateExampleRow(): array
    {
        return [
            'Mario',
            'Rossi',
            'Componente',
            'M',
            'mario.rossi@example.com',
            '+39 0541 123456',
            '+39 333 1234567',
            '47814',
            'Bellaria',
            '',
            'Via Roma',
            '12',
            '',
            '1982-07-14',
            'Italiana',
            '',
            '',
            '',
            'Carta d\'identita',
            'CA1234567',
            '2021-05-20',
            '2031-05-20',
            'Comune di Bellaria-Igea Marina',
            'Gruppo Primavera',
            '',
            '',
            '',
            'Arrivo gruppo scuola',
        ];
    }

    public function createBatchFromUploadedCsv(UploadedFile $file, Struttura $struttura, User $user): CustomerImportBatch
    {
        $storedPath = $file->storeAs(
            'customer-imports/' . $struttura->id,
            now()->format('Ymd_His') . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.csv'
        );

        if ($storedPath === false) {
            throw new \RuntimeException('Impossibile salvare il file importato.');
        }

        $absolutePath = Storage::path($storedPath);
        [$delimiter, $headers, $rows] = $this->parseCsv($absolutePath);
        $normalizedHeaders = $this->normalizeHeaders($headers);
        $entries = [];

        foreach ($rows as $index => $row) {
            $assoc = $this->rowToAssoc($normalizedHeaders, $row);
            if ($this->rowIsEmpty($assoc)) {
                continue;
            }

            $entries[] = [
                'row_number' => $index + 2,
                'raw_payload' => $assoc,
                'normalized_payload' => $this->normalizePayload($assoc, $struttura),
            ];
        }

        $fileSignatureCounts = $this->buildFileSignatureCounts($entries);

        return DB::transaction(function () use ($struttura, $user, $file, $storedPath, $delimiter, $entries, $fileSignatureCounts) {
            $batch = CustomerImportBatch::query()->create([
                'struttura_id' => $struttura->id,
                'proprietario_id' => $struttura->proprietario_id,
                'user_id' => $user->id,
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $storedPath,
                'status' => self::STATUS_DRAFT,
                'meta' => [
                    'delimiter' => $delimiter,
                    'headers' => $this->templateHeaders(),
                ],
            ]);

            foreach ($entries as $entry) {
                [$status, $notes, $duplicateId, $duplicateScope] = $this->evaluatePayload(
                    $entry['normalized_payload'],
                    $struttura,
                    $fileSignatureCounts
                );

                CustomerImportRow::query()->create([
                    'batch_id' => $batch->id,
                    'row_number' => $entry['row_number'],
                    'status' => $status,
                    'raw_payload' => $entry['raw_payload'],
                    'normalized_payload' => $entry['normalized_payload'],
                    'notes' => $notes,
                    'duplicate_customer_id' => $duplicateId,
                    'duplicate_scope' => $duplicateScope,
                ]);
            }

            $this->refreshBatchCounters($batch);

            return $batch->fresh('rows');
        });
    }

    public function recomputeRow(CustomerImportRow $row): CustomerImportRow
    {
        $batch = $row->batch()->with('struttura')->firstOrFail();
        $allRows = $batch->rows()->get(['id', 'normalized_payload']);

        $entries = $allRows->map(function (CustomerImportRow $item) {
            return [
                'id' => $item->id,
                'normalized_payload' => $item->normalized_payload ?? [],
            ];
        })->all();

        $fileSignatureCounts = $this->buildFileSignatureCounts($entries);
        [$status, $notes, $duplicateId, $duplicateScope] = $this->evaluatePayload(
            $row->normalized_payload ?? [],
            $batch->struttura,
            $fileSignatureCounts
        );

        $row->update([
            'status' => $status,
            'notes' => $notes,
            'duplicate_customer_id' => $duplicateId,
            'duplicate_scope' => $duplicateScope,
        ]);

        $this->refreshBatchCounters($batch);

        return $row->fresh();
    }

    public function commitBatch(CustomerImportBatch $batch): array
    {
        $imported = 0;

        DB::transaction(function () use ($batch, &$imported) {
            $rows = $batch->rows()
                ->where('status', self::STATUS_VALID)
                ->whereNull('imported_customer_id')
                ->get();

            foreach ($rows as $row) {
                $payload = $row->normalized_payload ?? [];
                $customer = Customers::query()->create($this->buildCustomerPayloadForImport($payload, $batch->struttura_id));
                $this->ensureNumeroCliente($customer, (string) ($payload['tipo_cliente'] ?? 'Componente'));
                $customer->save();

                $row->update([
                    'status' => self::STATUS_IMPORTED,
                    'imported_customer_id' => $customer->id,
                    'imported_at' => now(),
                ]);

                $imported++;
            }

            $this->refreshBatchCounters($batch);
            $batch->update([
                'status' => $imported > 0 ? self::STATUS_IMPORTED : $batch->status,
            ]);
        });

        return [
            'imported' => $imported,
            'remaining_valid' => $batch->rows()->where('status', self::STATUS_VALID)->whereNull('imported_customer_id')->count(),
        ];
    }

    public function updateRowPayload(CustomerImportRow $row, array $input): CustomerImportRow
    {
        $batch = $row->batch()->with('struttura')->firstOrFail();
        $normalized = $this->normalizePayload($input, $batch->struttura);

        $row->update([
            'normalized_payload' => $normalized,
        ]);

        return $this->recomputeRow($row);
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Impossibile aprire il file CSV.');
        }

        $firstLine = fgets($handle);
        fclose($handle);

        if ($firstLine === false) {
            throw new \RuntimeException('Il file CSV e vuoto.');
        }

        $delimiter = $this->detectDelimiter($firstLine);
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Impossibile leggere il file CSV.');
        }

        $headers = fgetcsv($handle, 0, $delimiter);
        if (!is_array($headers)) {
            fclose($handle);
            throw new \RuntimeException('Header CSV non valido.');
        }

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return [$delimiter, $headers, $rows];
    }

    private function detectDelimiter(string $line): string
    {
        $delimiters = [';', ',', "\t"];
        $scores = [];

        foreach ($delimiters as $delimiter) {
            $scores[$delimiter] = substr_count($line, $delimiter);
        }

        arsort($scores);
        $best = array_key_first($scores);

        return is_string($best) ? $best : ';';
    }

    private function normalizeHeaders(array $headers): array
    {
        $aliases = [
            'nome' => 'nome',
            'cognome' => 'cognome',
            'tipo_cliente' => 'tipo_cliente',
            'tipo cliente' => 'tipo_cliente',
            'sesso' => 'sesso',
            'sex' => 'sesso',
            'email' => 'email',
            'telefono' => 'telefono',
            'cellulare' => 'cellulare',
            'cap_residenza' => 'cap_residenza',
            'cap residenza' => 'cap_residenza',
            'comune_residenza' => 'comune_residenza',
            'citta_residenza' => 'comune_residenza',
            'città_residenza' => 'comune_residenza',
            'provincia_residenza' => 'provincia_residenza',
            'indirizzo_residenza' => 'indirizzo_residenza',
            'numero_civico_residenza' => 'numero_civico_residenza',
            'numero residenza' => 'numero_civico_residenza',
            'nazione_residenza' => 'nazione_residenza',
            'data_nascita' => 'data_nascita',
            'cittadinanza' => 'cittadinanza',
            'nazionalita' => 'cittadinanza',
            'nazionalità' => 'cittadinanza',
            'nazione_nascita' => 'nazione_nascita',
            'comune_nascita' => 'comune_nascita',
            'citta_nascita' => 'comune_nascita',
            'città_nascita' => 'comune_nascita',
            'provincia_nascita' => 'provincia_nascita',
            'tipo_documento' => 'tipo_documento',
            'numero_documento' => 'numero_documento',
            'data_rilascio' => 'data_rilascio',
            'data_scadenza' => 'data_scadenza',
            'rilasciato_da' => 'rilasciato_da',
            'nome_gruppo' => 'nome_gruppo',
            'gruppo' => 'gruppo',
            'subgroup' => 'subgroup',
            'subgroup1' => 'subgroup1',
            'note' => 'note',
        ];

        return array_map(function ($header) use ($aliases) {
            $key = Str::of((string) $header)
                ->lower()
                ->ascii()
                ->replace(['-', '/', '\\'], ' ')
                ->replaceMatches('/\s+/', '_')
                ->trim('_')
                ->value();

            return $aliases[$key] ?? $key;
        }, $headers);
    }

    private function rowToAssoc(array $headers, array $row): array
    {
        $headersCount = count($headers);
        $rowCount = count($row);

        if ($rowCount < $headersCount) {
            $row = array_pad($row, $headersCount, '');
        } elseif ($rowCount > $headersCount) {
            $row = array_slice($row, 0, $headersCount);
        }

        $assoc = array_combine($headers, $row);

        return $assoc === false ? [] : $assoc;
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizePayload(array $raw, Struttura $struttura): array
    {
        $payload = [];
        foreach ($this->templateHeaders() as $field) {
            $payload[$field] = trim((string) ($raw[$field] ?? ''));
        }

        $payload['tipo_cliente'] = $this->normalizeTipoCliente($payload['tipo_cliente'] ?: 'Componente');
        $payload['sesso'] = strtoupper(substr($payload['sesso'], 0, 1));
        $payload['telefono'] = $this->normalizePhone($payload['telefono']);
        $payload['cellulare'] = $this->normalizePhone($payload['cellulare']);
        $payload['email'] = Str::lower($payload['email']);
        $payload['data_nascita'] = $this->normalizeDate($payload['data_nascita']);
        $payload['data_rilascio'] = $this->normalizeDate($payload['data_rilascio']);
        $payload['data_scadenza'] = $this->normalizeDate($payload['data_scadenza']);
        $payload['cap_residenza'] = preg_replace('/\D+/', '', $payload['cap_residenza']) ?: '';
        $payload['numero_documento'] = strtoupper(preg_replace('/\s+/', '', $payload['numero_documento']));

        $payload['cittadinanza'] = $this->normalizeCittadinanzaValue($payload['cittadinanza']);
        $payload['nazione_residenza'] = $this->normalizeCountryNameValue($payload['nazione_residenza']);
        $payload['nazione_nascita'] = $this->normalizeCountryNameValue($payload['nazione_nascita']);
        $payload['tipo_documento'] = $this->normalizeTipoDocumento($payload['tipo_documento']);
        $payload['rilasciato_da'] = $this->normalizeRilasciatoDa($payload['rilasciato_da']);

        $payload = $this->normalizeResidenceByCap($payload, $struttura);
        $payload['comune_nascita'] = $this->normalizeComuneName($payload['comune_nascita']);
        $payload['provincia_nascita'] = $this->normalizeProvinciaValue($payload['provincia_nascita']);

        return $payload;
    }

    private function buildFileSignatureCounts(array $entries): array
    {
        $counts = [];

        foreach ($entries as $entry) {
            $payload = $entry['normalized_payload'] ?? [];
            foreach ($this->signaturesForPayload($payload) as $signature) {
                $counts[$signature] = ($counts[$signature] ?? 0) + 1;
            }
        }

        return $counts;
    }

    private function evaluatePayload(array $payload, Struttura $struttura, array $fileSignatureCounts): array
    {
        $notes = [];
        $duplicateId = null;
        $duplicateScope = null;

        $missingRequired = $this->missingRequiredFields($payload);
        if (!empty($missingRequired)) {
            $notes[] = 'Dati da completare: ' . implode(', ', $missingRequired) . '.';
        }

        if (!empty($payload['cap_residenza']) && empty($payload['comune_residenza'])) {
            $notes[] = 'CAP presente ma comune non normalizzato: verifica il GEO.';
        }

        foreach ($this->signaturesForPayload($payload) as $signature) {
            if (($fileSignatureCounts[$signature] ?? 0) > 1) {
                $notes[] = 'Possibile duplicato interno nello stesso file.';
                return [self::STATUS_DUPLICATE_FILE, $notes, null, 'file'];
            }
        }

        [$duplicateId, $duplicateScope] = $this->findPotentialDuplicate($payload, $struttura);

        if (!empty($missingRequired)) {
            return [self::STATUS_NEEDS_REVIEW, $notes, $duplicateId, $duplicateScope];
        }

        if ($duplicateScope === 'hotel') {
            $notes[] = 'Cliente gia presente in questa struttura.';
            return [self::STATUS_DUPLICATE_HOTEL, $notes, $duplicateId, $duplicateScope];
        }

        if ($duplicateScope === 'chain') {
            $notes[] = 'Cliente trovato in un\'altra struttura della stessa catena.';
            return [self::STATUS_DUPLICATE_CHAIN, $notes, $duplicateId, $duplicateScope];
        }

        if (empty($notes)) {
            $notes[] = 'Riga valida e pronta per essere salvata in Clienti.';
        }

        return [self::STATUS_VALID, $notes, null, null];
    }

    private function missingRequiredFields(array $payload): array
    {
        $labels = [
            'nome' => 'Nome',
            'cognome' => 'Cognome',
            'tipo_cliente' => 'Tipo cliente',
            'sesso' => 'Sesso',
            'data_nascita' => 'Data di nascita',
            'cittadinanza' => 'Cittadinanza',
            'tipo_documento' => 'Tipo documento',
            'numero_documento' => 'Numero documento',
        ];

        $missing = [];
        foreach ($labels as $field => $label) {
            if (trim((string) ($payload[$field] ?? '')) === '') {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    private function findPotentialDuplicate(array $payload, Struttura $struttura): array
    {
        $query = Customers::query()->withoutGlobalScopes()->with('struttura:id,nome_struttura')->orderByDesc('id');
        $currentStructureId = (int) $struttura->id;
        $sameChainIds = Struttura::query()
            ->when($struttura->proprietario_id, fn ($q) => $q->where('proprietario_id', $struttura->proprietario_id))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (!empty($payload['tipo_documento']) && !empty($payload['numero_documento'])) {
            $customer = (clone $query)
                ->whereIn('struttura_id', $sameChainIds ?: [$currentStructureId])
                ->where('type_doc_reg', $payload['tipo_documento'])
                ->where('num_doc_reg', $payload['numero_documento'])
                ->first();

            if ($customer) {
                return [$customer->id, (int) $customer->struttura_id === $currentStructureId ? 'hotel' : 'chain'];
            }
        }

        if (!empty($payload['email'])) {
            $customer = (clone $query)
                ->whereIn('struttura_id', $sameChainIds ?: [$currentStructureId])
                ->where('email', $payload['email'])
                ->first();

            if ($customer) {
                return [$customer->id, (int) $customer->struttura_id === $currentStructureId ? 'hotel' : 'chain'];
            }
        }

        $phoneCandidate = $payload['cellulare'] ?: $payload['telefono'];
        if ($phoneCandidate !== '') {
            $customer = (clone $query)
                ->whereIn('struttura_id', $sameChainIds ?: [$currentStructureId])
                ->where(function ($inner) use ($phoneCandidate) {
                    $inner->where('cellphone', $phoneCandidate)->orWhere('phone', $phoneCandidate);
                })
                ->first();

            if ($customer) {
                return [$customer->id, (int) $customer->struttura_id === $currentStructureId ? 'hotel' : 'chain'];
            }
        }

        if (!empty($payload['nome']) && !empty($payload['cognome']) && !empty($payload['data_nascita'])) {
            $customer = (clone $query)
                ->whereIn('struttura_id', $sameChainIds ?: [$currentStructureId])
                ->where('name', $payload['nome'])
                ->where('surname', $payload['cognome'])
                ->where('nac_reg', $payload['data_nascita'])
                ->first();

            if ($customer) {
                return [$customer->id, (int) $customer->struttura_id === $currentStructureId ? 'hotel' : 'chain'];
            }
        }

        return [null, null];
    }

    private function signaturesForPayload(array $payload): array
    {
        $signatures = [];

        if (!empty($payload['tipo_documento']) && !empty($payload['numero_documento'])) {
            $signatures[] = 'doc:' . Str::lower($payload['tipo_documento']) . ':' . Str::lower($payload['numero_documento']);
        }

        if (!empty($payload['email'])) {
            $signatures[] = 'email:' . Str::lower($payload['email']);
        }

        if (!empty($payload['cellulare'])) {
            $signatures[] = 'cell:' . $payload['cellulare'];
        }

        if (!empty($payload['telefono'])) {
            $signatures[] = 'phone:' . $payload['telefono'];
        }

        if (!empty($payload['nome']) && !empty($payload['cognome']) && !empty($payload['data_nascita'])) {
            $signatures[] = 'person:' . Str::lower($payload['nome']) . ':' . Str::lower($payload['cognome']) . ':' . $payload['data_nascita'];
        }

        return array_values(array_unique($signatures));
    }

    private function normalizeResidenceByCap(array $payload, Struttura $struttura): array
    {
        if (empty($payload['cap_residenza'])) {
            $payload['comune_residenza'] = $this->normalizeComuneName($payload['comune_residenza']);
            $payload['provincia_residenza'] = $this->normalizeProvinciaValue($payload['provincia_residenza']);
            $payload['nazione_residenza'] = $payload['nazione_residenza'] ?: 'Italia';

            return $payload;
        }

        $cap = GeoCap::query()->where('cap', $payload['cap_residenza'])->first();
        if (!$cap) {
            $payload['comune_residenza'] = $this->normalizeComuneName($payload['comune_residenza']);
            $payload['provincia_residenza'] = $this->normalizeProvinciaValue($payload['provincia_residenza']);
            $payload['nazione_residenza'] = $payload['nazione_residenza'] ?: 'Italia';

            return $payload;
        }

        $comune = GeoComune::query()
            ->select('geo_comuni.*')
            ->join('geo_comuni_cap', 'geo_comuni_cap.comune_id', '=', 'geo_comuni.id')
            ->where('geo_comuni_cap.cap_id', $cap->id)
            ->orderBy('geo_comuni.nome')
            ->first();

        if (!$comune) {
            return $payload;
        }

        $provincia = $comune->provincia_id ? GeoProvincia::query()->find($comune->provincia_id) : null;
        $payload['comune_residenza'] = $comune->nome;
        $payload['provincia_residenza'] = $provincia?->sigla ?: ($provincia?->nome ?: $payload['provincia_residenza']);
        $payload['nazione_residenza'] = $payload['nazione_residenza'] ?: 'Italia';

        return $payload;
    }

    private function buildCustomerPayloadForImport(array $payload, int $strutturaId): array
    {
        $observation = trim(collect([
            $payload['note'] ?? null,
            !empty($payload['nome_gruppo']) ? 'Gruppo arrivo: ' . $payload['nome_gruppo'] : null,
        ])->filter()->implode(' | '));

        $data = [
            'struttura_id' => $strutturaId,
            'group' => $payload['gruppo'] ?: null,
            'subgroup' => $payload['subgroup'] ?: null,
            'subgroup1' => $payload['subgroup1'] ?: null,
            'sex' => $payload['sesso'] ?: null,
            'type_housed' => $payload['tipo_cliente'] ?: 'Componente',
            'name' => $payload['nome'] ?: null,
            'surname' => $payload['cognome'] ?: null,
            'country' => $payload['nazione_residenza'] ?: null,
            'city' => $payload['comune_residenza'] ?: null,
            'province' => $payload['provincia_residenza'] ?: null,
            'cap' => $payload['cap_residenza'] ?: null,
            'address' => $payload['indirizzo_residenza'] ?: null,
            'number' => $payload['numero_civico_residenza'] ?: null,
            'email' => $payload['email'] ?: null,
            'phone' => $payload['telefono'] ?: null,
            'cellphone' => $payload['cellulare'] ?: null,
            'observation' => $observation !== '' ? $observation : null,
            'country_reg' => $payload['nazione_nascita'] ?: null,
            'city_reg' => $payload['comune_nascita'] ?: null,
            'prov_reg' => $payload['provincia_nascita'] ?: null,
            'ciudadania_reg' => $payload['cittadinanza'] ?: null,
            'nac_reg' => $payload['data_nascita'] ?: null,
            'type_doc_reg' => $payload['tipo_documento'] ?: null,
            'num_doc_reg' => $payload['numero_documento'] ?: null,
            'date_pub_reg' => $payload['data_rilascio'] ?: null,
            'expire_reg' => $payload['data_scadenza'] ?: null,
            'rilasciato_reg' => $payload['rilasciato_da'] ?: null,
        ];

        return $this->filterClientiPayloadByExistingColumns($data);
    }

    private function ensureNumeroCliente(Customers $customer, string $tipoCliente): void
    {
        $prefix = match ($this->normalizeTipoCliente($tipoCliente)) {
            'Richiesta' => 'R',
            'Componente' => 'C',
            default => 'O',
        };

        $yearTwoDigits = $customer->created_at ? $customer->created_at->format('y') : now()->format('y');
        $pattern = "{$prefix}-{$yearTwoDigits}-%";

        $lastCode = Customers::query()
            ->withoutGlobalScopes()
            ->where('struttura_id', $customer->struttura_id)
            ->where('numero_cliente', 'like', $pattern)
            ->orderByDesc('numero_cliente')
            ->value('numero_cliente');

        $next = 1;
        if ($lastCode && preg_match('/-(\d{4})$/', $lastCode, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        $customer->numero_cliente = sprintf('%s-%s-%04d', $prefix, $yearTwoDigits, $next);
    }

    private function filterClientiPayloadByExistingColumns(array $payload): array
    {
        static $allowedColumns = null;

        if ($allowedColumns === null) {
            $allowedColumns = array_flip(Schema::getColumnListing('clienti'));
        }

        return array_intersect_key($payload, $allowedColumns);
    }

    private function normalizeTipoCliente(string $value): string
    {
        $key = Str::of($value)->lower()->ascii()->trim()->value();

        return match ($key) {
            'richiesta' => 'Richiesta',
            'ospite' => 'Ospite',
            default => 'Componente',
        };
    }

    private function normalizeDate(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }

    private function normalizePhone(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $clean = preg_replace('/[^\d\+]/', '', $value) ?: '';
        if ($clean !== '' && !str_starts_with($clean, '+') && str_starts_with($value, '+')) {
            $clean = '+' . $clean;
        }

        return $clean;
    }

    private function normalizeComuneName(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (ctype_digit($value)) {
            $comuneById = GeoComune::query()->find((int) $value);
            if ($comuneById) {
                return $comuneById->nome;
            }
        }

        $normalized = Str::of($value)->trim()->replaceMatches('/\s+/', ' ')->value();
        $comune = GeoComune::query()->whereRaw('LOWER(nome) = ?', [Str::lower($normalized)])->first();

        return $comune?->nome ?: $normalized;
    }

    private function normalizeProvinciaValue(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (ctype_digit($value)) {
            $provinciaById = GeoProvincia::query()->find((int) $value);
            if ($provinciaById) {
                return $provinciaById->sigla ?: $provinciaById->nome;
            }
        }

        $upper = Str::upper($value);
        $provincia = GeoProvincia::query()
            ->where('sigla', $upper)
            ->orWhereRaw('LOWER(nome) = ?', [Str::lower($value)])
            ->first();

        return $provincia?->sigla ?: $upper;
    }

    private function normalizeCountryNameValue(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (ctype_digit($value)) {
            $nazioneById = GeoNazione::query()->find((int) $value);
            if ($nazioneById) {
                return $nazioneById->nome;
            }
        }

        $nazione = GeoNazione::query()
            ->whereRaw('LOWER(nome) = ?', [Str::lower($value)])
            ->orWhereRaw('LOWER(cittadinanza) = ?', [Str::lower($value)])
            ->first();

        if (!$nazione) {
            return $value;
        }

        return $nazione->nome;
    }

    private function normalizeCittadinanzaValue(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (ctype_digit($value)) {
            $nazioneById = GeoNazione::query()->find((int) $value);
            if ($nazioneById) {
                return $nazioneById->cittadinanza ?: $nazioneById->nome;
            }
        }

        $nazione = GeoNazione::query()
            ->whereRaw('LOWER(nome) = ?', [Str::lower($value)])
            ->orWhereRaw('LOWER(cittadinanza) = ?', [Str::lower($value)])
            ->first();

        if (!$nazione) {
            return $value;
        }

        return $nazione->cittadinanza ?: $nazione->nome;
    }

    private function normalizeTipoDocumento(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (ctype_digit($value)) {
            $tipoById = TipoDocumento::query()->find((int) $value);
            if ($tipoById) {
                return $tipoById->descrizione;
            }
        }

        $tipo = TipoDocumento::query()
            ->whereRaw('LOWER(descrizione) = ?', [Str::lower($value)])
            ->first();

        return $tipo?->descrizione ?: $value;
    }

    private function normalizeRilasciatoDa(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (ctype_digit($value)) {
            $rilasciatoById = RilasciatoDa::query()->find((int) $value);
            if ($rilasciatoById) {
                return $rilasciatoById->name;
            }
        }

        $rilasciato = RilasciatoDa::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($value)])
            ->first();

        return $rilasciato?->name ?: $value;
    }

    private function refreshBatchCounters(CustomerImportBatch $batch): void
    {
        $rows = $batch->rows()->get(['status', 'imported_customer_id']);

        $batch->update([
            'total_rows' => $rows->count(),
            'valid_rows' => $rows->where('status', self::STATUS_VALID)->count(),
            'duplicate_hotel_rows' => $rows->where('status', self::STATUS_DUPLICATE_HOTEL)->count(),
            'duplicate_chain_rows' => $rows->where('status', self::STATUS_DUPLICATE_CHAIN)->count(),
            'duplicate_file_rows' => $rows->where('status', self::STATUS_DUPLICATE_FILE)->count(),
            'needs_review_rows' => $rows->where('status', self::STATUS_NEEDS_REVIEW)->count(),
            'imported_rows' => $rows->whereNotNull('imported_customer_id')->count(),
        ]);
    }
}
