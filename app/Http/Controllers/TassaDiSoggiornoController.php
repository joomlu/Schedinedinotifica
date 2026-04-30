<?php

namespace App\Http\Controllers;

use App\Models\TassaDiSoggiorno;
use App\Models\Struttura;
use App\Models\TassaEsenzione;
use App\Support\StrutturaCorrente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;



class TassaDiSoggiornoController extends Controller
{
    private const BELLARIA_DEFAULTS = [
        'tassa_soggiorno' => '1.50',
        'giorni_massimo' => 6,
        'inizio' => '2026-03-01',
        'fine' => '2026-10-01',
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
        if (!$strutturaId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $struttura = Struttura::findOrFail($strutturaId);
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

        return view('tassa_di_soggiorno.edit', compact('tassa', 'struttura', 'esenzioni', 'canManageEsenzioni'));
    }

    // Salva il record unico
    public function update(Request $request)
    {
        $strutturaId = StrutturaCorrente::getId() ?? $request->user()->struttura_id;
        if (!$strutturaId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
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
        ], [], [
            'tassa_soggiorno' => 'aliquota',
            'giorni_massimo' => 'giorni massimo imponibili',
            'inizio' => 'data inizio',
            'fine' => 'data fine',
            'max_age_children' => 'età massima bambini',
            'min_age_adult' => 'età minima adulti',
        ]);

        $hasInizio = !empty($data['inizio']);
        $hasFine = !empty($data['fine']);
        if ($hasInizio xor $hasFine) {
            return back()
                ->withErrors(['fine' => 'Per impostare il periodo di applicazione devi compilare sia la data di inizio sia la data di fine.'])
                ->withInput();
        }

        if (!empty($data['inizio']) && !empty($data['fine'])) {
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

        $tassa->update($data);
        return redirect()->route('tassa_di_soggiorno.edit')->with('success', 'Tassa di soggiorno aggiornata con successo');
    }

    private function resolveTassaConfigurazione(Struttura $struttura): TassaDiSoggiorno
    {
        $tassa = TassaDiSoggiorno::firstOrNew([
            'struttura_id' => $struttura->id,
        ]);

        if ($this->isBellariaIgeaMarina($struttura)) {
            $changed = false;

            foreach (self::BELLARIA_DEFAULTS as $field => $value) {
                $current = $tassa->{$field};
                if ($current === null || $current === '') {
                    $tassa->{$field} = $value;
                    $changed = true;
                }
            }

            if (!$tassa->exists || $changed) {
                $tassa->save();
            }

            return $tassa->fresh();
        }

        if (!$tassa->exists) {
            $tassa->save();
        }

        return $tassa;
    }

    private function syncComuneDefaults(Struttura $struttura): void
    {
        if (!$this->isBellariaIgeaMarina($struttura) || !Schema::hasTable('tassa_esenzioni')) {
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
