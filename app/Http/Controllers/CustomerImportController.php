<?php

namespace App\Http\Controllers;

use App\Models\CustomerImportBatch;
use App\Models\CustomerImportRow;
use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use App\Models\GeoRegione;
use App\Models\Gruppo;
use App\Models\RilasciatoDa;
use App\Models\Struttura;
use App\Models\TipoCliente;
use App\Models\TipoDocumento;
use App\Models\TipoVia;
use App\Models\Titolo;
use App\Services\CustomerImportService;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;

class CustomerImportController extends Controller
{
    public function __construct(private readonly CustomerImportService $service)
    {
    }

    public function index()
    {
        $struttura = $this->currentStruttura();
        $batches = CustomerImportBatch::query()
            ->where('struttura_id', $struttura->id)
            ->latest()
            ->paginate(10);

        return view('customers.import.index', [
            'struttura' => $struttura,
            'batches' => $batches,
        ]);
    }

    public function template()
    {
        $headers = $this->service->templateHeaders();
        $example = $this->service->templateExampleRow();

        $callback = function () use ($headers, $example) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers, ';');
            fputcsv($handle, $example, ';');
            fclose($handle);
        };

        return response()->streamDownload($callback, 'modello_import_clienti.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file_import' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], [
            'file_import.required' => 'Seleziona un file CSV da importare.',
            'file_import.mimes' => 'Il file deve essere un CSV o TXT separato da punto e virgola.',
            'file_import.max' => 'Il file importato e troppo grande.',
        ]);

        $batch = $this->service->createBatchFromUploadedCsv(
            $request->file('file_import'),
            $this->currentStruttura(),
            $request->user()
        );

        return redirect()
            ->route('customer.import.show', $batch)
            ->with('success', 'File importato nel settore di verifica. Controlla le righe valide prima di salvarle in Clienti.');
    }

    public function show(CustomerImportBatch $batch)
    {
        $batch = $this->loadOwnedBatch($batch->id);

        $rows = $batch->rows()
            ->with(['duplicateCustomer.struttura', 'importedCustomer'])
            ->paginate(20);

        return view('customers.import.show', [
            'batch' => $batch,
            'rows' => $rows,
            'statusLabels' => $this->statusLabels(),
            'statusClasses' => $this->statusClasses(),
        ]);
    }

    public function editRow(CustomerImportBatch $batch, CustomerImportRow $row)
    {
        [$batch, $row] = $this->loadOwnedBatchAndRow($batch->id, $row->id);

        $customerDraft = $this->makeCustomerDraftFromImportPayload($row->normalized_payload ?? []);

        return view('customers.import.edit-row', array_merge($this->buildSchedaClienteData(), [
            'batch' => $batch,
            'row' => $row,
            'customerDraft' => $customerDraft,
        ]));
    }

    public function updateRow(Request $request, CustomerImportBatch $batch, CustomerImportRow $row)
    {
        [$batch, $row] = $this->loadOwnedBatchAndRow($batch->id, $row->id);

        $validated = $request->validate([
            'type_cliente' => ['nullable', Rule::in(['Ospite', 'Componente', 'Richiesta'])],
            'name' => ['nullable', 'string', 'max:191'],
            'surname' => ['nullable', 'string', 'max:191'],
            'sex' => ['nullable', Rule::in(['M', 'F'])],
            'email' => ['nullable', 'string', 'max:191'],
            'telefono' => ['nullable', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:191'],
            'cellphone' => ['nullable', 'string', 'max:191'],
            'cap' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:191'],
            'province' => ['nullable', 'string', 'max:191'],
            'address' => ['nullable', 'string', 'max:191'],
            'number' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:191'],
            'nac_reg' => ['nullable', 'string', 'max:50'],
            'ciudadania_reg' => ['nullable', 'string', 'max:191'],
            'country_reg' => ['nullable', 'string', 'max:191'],
            'city_reg' => ['nullable', 'string', 'max:191'],
            'prov_reg' => ['nullable', 'string', 'max:191'],
            'type_doc_reg' => ['nullable', 'string', 'max:191'],
            'num_doc_reg' => ['nullable', 'string', 'max:191'],
            'date_pub_reg' => ['nullable', 'string', 'max:50'],
            'expire_reg' => ['nullable', 'string', 'max:50'],
            'rilasciato_reg' => ['nullable', 'string', 'max:191'],
            'observation' => ['nullable', 'string', 'max:5000'],
            'gruppo' => ['nullable', 'string', 'max:191'],
            'subgroup' => ['nullable', 'string', 'max:191'],
            'subgroup1' => ['nullable', 'string', 'max:191'],
            'group' => ['nullable', 'string', 'max:191'],
        ]);

        $this->service->updateRowPayload($row, $this->mapCustomerFormToImportPayload($validated));

        return redirect()
            ->route('customer.import.show', $batch)
            ->with('success', 'Riga importazione aggiornata e ricalcolata.');
    }

    public function commit(CustomerImportBatch $batch)
    {
        $batch = $this->loadOwnedBatch($batch->id);
        $result = $this->service->commitBatch($batch);

        return redirect()
            ->route('customer.import.show', $batch)
            ->with('success', 'Importazione completata: ' . $result['imported'] . ' clienti salvati in Clienti.');
    }

    private function currentStruttura(): Struttura
    {
        $strutturaId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        abort_if(!$strutturaId, 403, 'Nessuna struttura corrente disponibile.');

        return Struttura::query()->findOrFail($strutturaId);
    }

    private function loadOwnedBatch(int $batchId): CustomerImportBatch
    {
        return CustomerImportBatch::query()
            ->where('struttura_id', $this->currentStruttura()->id)
            ->findOrFail($batchId);
    }

    private function loadOwnedBatchAndRow(int $batchId, int $rowId): array
    {
        $batch = $this->loadOwnedBatch($batchId);
        $row = $batch->rows()->findOrFail($rowId);

        return [$batch, $row];
    }

    private function statusLabels(): array
    {
        return [
            CustomerImportService::STATUS_DRAFT => 'Bozza',
            CustomerImportService::STATUS_VALID => 'Valida',
            CustomerImportService::STATUS_NEEDS_REVIEW => 'Da completare',
            CustomerImportService::STATUS_DUPLICATE_FILE => 'Duplicato file',
            CustomerImportService::STATUS_DUPLICATE_HOTEL => 'Duplicato hotel',
            CustomerImportService::STATUS_DUPLICATE_CHAIN => 'Duplicato catena',
            CustomerImportService::STATUS_IMPORTED => 'Importata',
        ];
    }

    private function statusClasses(): array
    {
        return [
            CustomerImportService::STATUS_DRAFT => 'bg-secondary-subtle text-secondary',
            CustomerImportService::STATUS_VALID => 'bg-success-subtle text-success',
            CustomerImportService::STATUS_NEEDS_REVIEW => 'bg-warning-subtle text-warning',
            CustomerImportService::STATUS_DUPLICATE_FILE => 'bg-danger-subtle text-danger',
            CustomerImportService::STATUS_DUPLICATE_HOTEL => 'bg-danger-subtle text-danger',
            CustomerImportService::STATUS_DUPLICATE_CHAIN => 'bg-info-subtle text-info',
            CustomerImportService::STATUS_IMPORTED => 'bg-primary-subtle text-primary',
        ];
    }

    private function buildSchedaClienteData(): array
    {
        $groups = Gruppo::query()->orderBy('livello')->orderBy('nome')->get(['id', 'nome', 'livello', 'parent_id']);

        return [
            'groups' => $groups,
            'gruppiLivello1' => $groups->where('livello', 1)->values(),
            'gruppiLivello2' => $groups->where('livello', 2)->values(),
            'gruppiLivello3' => $groups->where('livello', 3)->values(),
            'tipiClienti' => TipoCliente::query()->where('attivo', true)->orderBy('id')->get(['id', 'codice', 'descrizione']),
            'titoli' => Titolo::query()->when(\Illuminate\Support\Facades\Schema::hasColumn('titolo', 'attivo'), fn ($query) => $query->where('attivo', true))->orderBy('nome')->get(['id', 'nome']),
            'tipiVia' => TipoVia::query()->orderBy('nome')->get(['id', 'nome as name']),
            'tipiDocumento' => TipoDocumento::query()->orderBy('descrizione')->get(['id', 'descrizione as name']),
            'nations' => GeoNazione::query()->orderBy('nome')->get(['id', 'nome', 'cittadinanza', 'codice_iso2']),
            'regions' => GeoRegione::query()->orderBy('nome')->get(['id', 'nome']),
            'provinces' => GeoProvincia::query()->orderBy('nome')->get(['id', 'nome', 'sigla']),
            'ciudades' => GeoComune::query()->orderBy('nome')->get(['id', 'nome']),
            'rilasciatoDa' => RilasciatoDa::query()->orderBy('name')->get(['id', 'name']),
            'cittadinanze' => GeoNazione::query()->whereNotNull('cittadinanza')->where('cittadinanza', '<>', '')->orderBy('cittadinanza')->pluck('cittadinanza')->unique()->values(),
            'geoNazioni' => GeoNazione::query()->orderBy('nome')->get(['id', 'nome', 'cittadinanza', 'codice_iso2', 'is_italia']),
        ];
    }

    private function makeCustomerDraftFromImportPayload(array $payload): Fluent
    {
        return new Fluent([
            'type_housed' => $payload['tipo_cliente'] ?? 'Componente',
            'type_cliente' => $payload['tipo_cliente'] ?? 'Componente',
            'name' => $payload['nome'] ?? null,
            'surname' => $payload['cognome'] ?? null,
            'sex' => $payload['sesso'] ?? null,
            'email' => $payload['email'] ?? null,
            'phone' => $payload['telefono'] ?? null,
            'cellphone' => $payload['cellulare'] ?? null,
            'cap' => $payload['cap_residenza'] ?? null,
            'city' => $payload['comune_residenza'] ?? null,
            'province' => $payload['provincia_residenza'] ?? null,
            'address' => $payload['indirizzo_residenza'] ?? null,
            'number' => $payload['numero_civico_residenza'] ?? null,
            'country' => $payload['nazione_residenza'] ?? null,
            'nac_reg' => $payload['data_nascita'] ?? null,
            'ciudadania_reg' => $payload['cittadinanza'] ?? null,
            'country_reg' => $payload['nazione_nascita'] ?? null,
            'city_reg' => $payload['comune_nascita'] ?? null,
            'prov_reg' => $payload['provincia_nascita'] ?? null,
            'type_doc_reg' => $payload['tipo_documento'] ?? null,
            'num_doc_reg' => $payload['numero_documento'] ?? null,
            'date_pub_reg' => $payload['data_rilascio'] ?? null,
            'expire_reg' => $payload['data_scadenza'] ?? null,
            'rilasciato_reg' => $payload['rilasciato_da'] ?? null,
            'group' => $payload['gruppo'] ?? null,
            'subgroup' => $payload['subgroup'] ?? null,
            'subgroup1' => $payload['subgroup1'] ?? null,
            'observation' => $payload['note'] ?? null,
        ]);
    }

    private function mapCustomerFormToImportPayload(array $validated): array
    {
        return [
            'nome' => $validated['name'] ?? '',
            'cognome' => $validated['surname'] ?? '',
            'tipo_cliente' => $validated['type_cliente'] ?? 'Componente',
            'sesso' => $validated['sex'] ?? '',
            'email' => $validated['email'] ?? '',
            'telefono' => $validated['phone'] ?? '',
            'cellulare' => $validated['cellphone'] ?? '',
            'cap_residenza' => $validated['cap'] ?? '',
            'comune_residenza' => $validated['city'] ?? '',
            'provincia_residenza' => $validated['province'] ?? '',
            'indirizzo_residenza' => $validated['address'] ?? '',
            'numero_civico_residenza' => $validated['number'] ?? '',
            'nazione_residenza' => $validated['country'] ?? '',
            'data_nascita' => $validated['nac_reg'] ?? '',
            'cittadinanza' => $validated['ciudadania_reg'] ?? '',
            'nazione_nascita' => $validated['country_reg'] ?? '',
            'comune_nascita' => $validated['city_reg'] ?? '',
            'provincia_nascita' => $validated['prov_reg'] ?? '',
            'tipo_documento' => $validated['type_doc_reg'] ?? '',
            'numero_documento' => $validated['num_doc_reg'] ?? '',
            'data_rilascio' => $validated['date_pub_reg'] ?? '',
            'data_scadenza' => $validated['expire_reg'] ?? '',
            'rilasciato_da' => $validated['rilasciato_reg'] ?? '',
            'nome_gruppo' => '',
            'gruppo' => $validated['group'] ?? ($validated['gruppo'] ?? ''),
            'subgroup' => $validated['subgroup'] ?? '',
            'subgroup1' => $validated['subgroup1'] ?? '',
            'note' => $validated['observation'] ?? '',
        ];
    }
}
