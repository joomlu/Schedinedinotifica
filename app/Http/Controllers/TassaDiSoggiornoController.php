<?php

namespace App\Http\Controllers;

use App\Models\Struttura;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Support\StrutturaCorrente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TassaDiSoggiornoController extends Controller
{
    private const BELLARIA_DEFAULTS = [
        'tassa_soggiorno' => '1.50',
        'giorni_massimo' => 6,
        'inizio' => '2026-06-01',
        'fine' => '2026-09-30',
        'max_age_children' => 17,
        'min_age_adult' => 18,
    ];

    private const BELLARIA_ESENZIONI = [
        ['codice' => '400', 'descrizione' => 'Minori fino al compimento del 18° anno di età', 'richiede_nota' => false, 'ordine' => 10],
        ['codice' => '405', 'descrizione' => 'Soggetti in terapia e accompagnatori', 'richiede_nota' => true, 'ordine' => 20],
        ['codice' => '410', 'descrizione' => 'Soggetti invalidi e accompagnatore', 'richiede_nota' => true, 'ordine' => 30],
        ['codice' => '415', 'descrizione' => 'Volontari in eventi organizzati o di emergenza', 'richiede_nota' => true, 'ordine' => 40],
        ['codice' => '420', 'descrizione' => 'Soggetti coinvolti in eventi calamitosi o di emergenza', 'richiede_nota' => true, 'ordine' => 50],
        ['codice' => '425', 'descrizione' => 'Autisti di pullman e accompagnatori turistici', 'richiede_nota' => false, 'ordine' => 60],
        ['codice' => '430', 'descrizione' => 'Personale dipendente della struttura ricettiva', 'richiede_nota' => false, 'ordine' => 70],
        ['codice' => '440', 'descrizione' => 'Forze Armate e Vigili del Fuoco in servizio', 'richiede_nota' => true, 'ordine' => 80],
        ['codice' => '450', 'descrizione' => 'Familiari del gestore se anagraficamente conviventi', 'richiede_nota' => true, 'ordine' => 85],
    ];

    // Redirect /create a edit
    public function create()
    {
        return redirect()->route('tassa_di_soggiorno.edit');
    }

    // Pagina unica di configurazione
    public function edit(Request $request)
    {
        $strutturaId = StrutturaCorrente::getId() ?? $request->user()->struttura_id;
        if (! $strutturaId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $struttura = Struttura::findOrFail($strutturaId);
        $automaticaBellaria = $this->isBellariaIgeaMarina($struttura) || blank($struttura->citta);
        if ($automaticaBellaria) {
            $service = app(\App\Services\TassaDiSoggiornoService::class);
            $tassa = TassaDiSoggiorno::where('struttura_id', $strutturaId)->first() ?? new TassaDiSoggiorno(['struttura_id' => $strutturaId]);
            $validati = $request->validate(['anno_fiscale' => 'nullable|integer|min:2015|max:2100']);
            $annoFiscale = (int) ($validati['anno_fiscale'] ?? now()->year);
            $diagnosi = $service->diagnosiConfigurazione($struttura, $tassa, $annoFiscale);
            $confronto = $diagnosi['profilo'] ? $service->confrontoConfigurazione($struttura, $tassa, $annoFiscale) : null;
            $puoRiallineare = $request->user()->canManageGestioneOperativa((int) $strutturaId) || $request->user()->isProprietario();
            $riallineamentoToken = $confronto && $puoRiallineare && $diagnosi['stato'] === 'configurazione_legacy_discordante'
                ? \Illuminate\Support\Facades\Crypt::encryptString(json_encode(['utente' => $request->user()->id, 'struttura' => (int) $strutturaId, 'anno' => $annoFiscale, 'scadenza' => now()->addMinutes(15)->timestamp, 'impronta' => $service->improntaConfigurazione($struttura, $tassa, $annoFiscale)], JSON_THROW_ON_ERROR)) : null;
            $datiStruttura = $service->datiStrutturaFiscali($struttura);
            $esenzioni = $service->catalogoBellaria($struttura, Schema::hasTable('tassa_esenzioni') ? TassaEsenzione::where('struttura_id', $strutturaId)->where('codice', '<>', '777')->get() : collect());
            $previewSchedine = \App\Models\Schedina::where('struttura_id', $strutturaId)->orderByDesc('id')->limit(20)->get();
            $previewSchedina = $previewSchedine->first();
            $comune = \App\Models\GeoComune::where('nome', $struttura->citta)->first();
            $logoComune = $comune?->logo_citta ?: ($comune?->logo ?: $struttura->logo_citta);

            return view('tassa_di_soggiorno.edit', compact('tassa', 'struttura', 'automaticaBellaria', 'diagnosi', 'datiStruttura', 'esenzioni', 'previewSchedina', 'previewSchedine', 'logoComune', 'annoFiscale', 'confronto', 'riallineamentoToken'));
        }
        $tassa = $this->resolveTassaConfigurazione($struttura);
        $this->syncComuneDefaults($struttura);

        $esenzioni = null;
        if (Schema::hasTable('tassa_esenzioni')) {
            $esenzioni = TassaEsenzione::where('struttura_id', $struttura->id)
                ->where('codice', '<>', '777')
                ->orderBy('ordine')
                ->orderBy('codice')
                ->paginate(10)
                ->withQueryString();
        }

        $canManageEsenzioni = $request->user()->isAdmin() || $request->user()->isSuperAdmin();

        return view('tassa_di_soggiorno.edit', compact('tassa', 'struttura', 'esenzioni', 'canManageEsenzioni', 'automaticaBellaria'));
    }

    public function riallinea(Request $request)
    {
        $sid = StrutturaCorrente::getId() ?? $request->user()->struttura_id;
        abort_unless($sid && ($request->user()->canManageGestioneOperativa((int) $sid) || $request->user()->isProprietario()), 403);
        $data = $request->validate(['consenso' => 'required|accepted', 'token' => 'required|string', 'struttura_id' => 'prohibited']);
        try {
            $preview = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($data['token']), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            abort(409, 'Anteprima non valida. Riaprire la configurazione.');
        }
        abort_unless(($preview['utente'] ?? null) === $request->user()->id && ($preview['struttura'] ?? null) === (int) $sid && ($preview['scadenza'] ?? 0) > now()->timestamp, 409, 'Anteprima scaduta o appartenente a un altro contesto.');
        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $sid, $preview) {
            $struttura = Struttura::whereKey($sid)->lockForUpdate()->firstOrFail();
            $tassa = TassaDiSoggiorno::where('struttura_id', $sid)->lockForUpdate()->first();
            $service = app(\App\Services\TassaDiSoggiornoService::class);
            abort_unless(hash_equals($preview['impronta'], $service->improntaConfigurazione($struttura, $tassa, $preview['anno'])), 409, 'Configurazione modificata dopo l’anteprima. Riaprire e verificare le differenze.');
            $confronto = $service->confrontoConfigurazione($struttura, $tassa, $preview['anno']);
            abort_unless($confronto['differenze'], 409, 'Configurazione già allineata.');
            $tassa ??= new TassaDiSoggiorno(['struttura_id' => $sid]);
            $tassa->forceFill($confronto['richiesti']);
            if (! $tassa->save()) {
                throw new \RuntimeException('Riallineamento non salvato.');
            }
            foreach (['prima' => $confronto['attuali'], 'dopo' => $confronto['richiesti']] as $fase => $valori) {
                $log = new \App\Models\StrutturaAuditLog([
                    'struttura_id' => $sid, 'user_id' => $request->user()->id, 'route_name' => 'tassa_di_soggiorno.riallinea', 'metodo' => 'POST',
                    'entita_tipo' => 'configurazione_tassa', 'entita_id' => $tassa->id,
                    'descrizione' => $fase.' '.$confronto['profilo']['regola_versione'].' '.json_encode($valori, JSON_THROW_ON_ERROR), 'created_at' => now(),
                ]);
                if (! $log->save()) {
                    throw new \RuntimeException('Tracciamento riallineamento non salvato.');
                }
            }
        });

        return redirect()->route('tassa_di_soggiorno.edit', ['anno_fiscale' => $preview['anno']])->with('success', 'Configurazione fiscale riallineata esplicitamente. Storico consolidato e ricevute salvate invariati.');
    }

    // Salva il record unico
    public function update(Request $request)
    {
        $strutturaId = StrutturaCorrente::getId() ?? $request->user()->struttura_id;
        if (! $strutturaId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $struttura = Struttura::findOrFail($strutturaId);
        if ($this->isBellariaIgeaMarina($struttura)) {
            $data = $request->validate([
                'tassa_soggiorno' => 'prohibited', 'giorni_massimo' => 'prohibited', 'inizio' => 'prohibited', 'fine' => 'prohibited',
                'max_age_children' => 'prohibited', 'min_age_adult' => 'prohibited', 'note' => 'prohibited',
                'ricevuta_immagine' => 'prohibited',
                'ricevuta_foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'ricevuta_senza_immagine' => 'nullable|boolean',
            ]);
            $tassa = TassaDiSoggiorno::where('struttura_id', $strutturaId)->first() ?? new TassaDiSoggiorno(['struttura_id' => $strutturaId]);
            $nuovoPath = null;
            try {
                if ($request->boolean('ricevuta_senza_immagine')) {
                    $tassa->ricevuta_immagine = null;
                } elseif ($request->hasFile('ricevuta_foto')) {
                    $nuovoPath = $request->file('ricevuta_foto')->store('tassa-ricevute/'.$strutturaId, 'local');
                    if (! $nuovoPath) {
                        throw new \RuntimeException('Salvataggio immagine ricevuta non riuscito.');
                    }
                    $tassa->ricevuta_immagine = $nuovoPath;
                }
                if ($tassa->isDirty('ricevuta_immagine')) {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($tassa) {
                        if (! $tassa->save()) {
                            throw new \RuntimeException('Salvataggio preferenze ricevuta non riuscito.');
                        }
                    });
                }
            } catch (\Throwable $exception) {
                if ($nuovoPath) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($nuovoPath);
                }
                throw $exception;
            }

            return redirect()->route('tassa_di_soggiorno.edit')->with('success', 'Preferenze della ricevuta aggiornate.');
        }
        $tassa = TassaDiSoggiorno::where('struttura_id', $strutturaId)->firstOrFail();

        $data = $request->validate([
            'tassa_soggiorno' => 'nullable|numeric|min:0|max:9999',
            'giorni_massimo' => 'nullable|integer|min:0|max:365',
            'inizio' => 'nullable|date',
            'fine' => 'nullable|date',
            'max_age_children' => 'nullable|integer|min:0|max:120',
            'min_age_adult' => 'nullable|integer|min:0|max:120',
            'note' => 'nullable|string',
            'ricevuta_foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'ricevuta_senza_immagine' => 'nullable|boolean',
        ], [], [
            'tassa_soggiorno' => 'aliquota',
            'giorni_massimo' => 'giorni massimo imponibili',
            'inizio' => 'data inizio',
            'fine' => 'data fine',
            'max_age_children' => 'età massima bambini',
            'min_age_adult' => 'età minima adulti',
        ]);

        $hasInizio = ! empty($data['inizio']);
        $hasFine = ! empty($data['fine']);
        if ($hasInizio xor $hasFine) {
            return back()
                ->withErrors(['fine' => 'Per impostare il periodo di applicazione devi compilare sia la data di inizio sia la data di fine.'])
                ->withInput();
        }

        if (! empty($data['inizio']) && ! empty($data['fine'])) {
            $inizio = Carbon::parse($data['inizio']);
            $fine = Carbon::parse($data['fine']);
            if ($fine->lessThan($inizio)) {
                return back()->withErrors(['fine' => 'La data di fine deve essere successiva o uguale alla data di inizio.'])->withInput();
            }
        }

        // Normalizza decimali con virgola
        if (isset($data['tassa_soggiorno'])) {
            $data['tassa_soggiorno'] = str_replace(',', '.', (string) $data['tassa_soggiorno']);
        }

        unset($data['ricevuta_foto'], $data['ricevuta_senza_immagine']);
        if ($request->boolean('ricevuta_senza_immagine')) {
            $data['ricevuta_immagine'] = null;
        } elseif ($request->hasFile('ricevuta_foto')) {
            $data['ricevuta_immagine'] = 'storage/'.$request->file('ricevuta_foto')->store('tassa-ricevute', 'public');
        }
        $tassa->update($data);

        return redirect()->route('tassa_di_soggiorno.edit')->with('success', 'Tassa di soggiorno aggiornata con successo');
    }

    public function immagine(Request $request)
    {
        $strutturaId = StrutturaCorrente::getId() ?? $request->user()->struttura_id;
        abort_unless($strutturaId, 404);
        if ($request->filled('export_id')) {
            $request->validate(['export_id' => 'integer|min:1']);
            $export = \App\Models\TassaExport::where('struttura_id', $strutturaId)->findOrFail($request->integer('export_id'));
            $snapshot = $export->snapshot;
            abort_unless(hash_equals($export->sha256, hash('sha256', $snapshot['csv'])), 409);
            $path = $snapshot['configurazione']['ricevuta_immagine'] ?? null;
        } else {
            $path = TassaDiSoggiorno::where('struttura_id', $strutturaId)->value('ricevuta_immagine');
        }
        abort_unless(is_string($path) && str_starts_with($path, 'tassa-ricevute/'.$strutturaId.'/') && ! str_contains($path, '..'), 404);
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        abort_unless($disk->exists($path), 404);
        $root = realpath($disk->path('tassa-ricevute/'.$strutturaId));
        $file = realpath($disk->path($path));
        abort_unless($root && $file && str_starts_with($file, $root.DIRECTORY_SEPARATOR), 404);

        return response()->file($file, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'])->setPrivate();
    }

    private function resolveTassaConfigurazione(Struttura $struttura): TassaDiSoggiorno
    {
        $tassa = TassaDiSoggiorno::firstOrNew([
            'struttura_id' => $struttura->id,
        ]);

        if ($this->isBellariaIgeaMarina($struttura)) {
            $changed = false;

            $defaults = self::BELLARIA_DEFAULTS;
            $regola = (new \App\Services\TassaDiSoggiornoService)->regolaAlbergoBellaria($struttura);
            if ($regola) {
                $defaults = array_replace($defaults, $regola);
            } else {
                unset($defaults['tassa_soggiorno'], $defaults['giorni_massimo']);
            }
            foreach ($defaults as $field => $value) {
                $current = $tassa->{$field};
                if ($current === null || $current === '') {
                    $tassa->{$field} = $value;
                    $changed = true;
                }
            }

            if (! $tassa->exists || $changed) {
                $tassa->save();
            }

            return $tassa->fresh();
        }

        if (! $tassa->exists) {
            $tassa->save();
        }

        return $tassa;
    }

    private function syncComuneDefaults(Struttura $struttura): void
    {
        if (! $this->isBellariaIgeaMarina($struttura) || ! Schema::hasTable('tassa_esenzioni')) {
            return;
        }

        foreach (self::BELLARIA_ESENZIONI as $row) {
            TassaEsenzione::firstOrCreate(
                [
                    'struttura_id' => $struttura->id,
                    'codice' => $row['codice'],
                ],
                [
                    'descrizione' => $row['descrizione'],
                    'richiede_nota' => $row['richiede_nota'],
                    'ordine' => $row['ordine'],
                    'attivo' => true,
                ]
            );
        }
    }

    private function isBellariaIgeaMarina(Struttura $struttura): bool
    {
        $comune = $this->normalizeComune($struttura->citta ?? '');

        return in_array($comune, [
            'bellaria-igea marina',
            'bellaria igea marina',
        ], true);
    }

    private function normalizeComune(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value) ?: '';

        return $value;
    }
}
