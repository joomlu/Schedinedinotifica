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
        $tassa = TassaDiSoggiorno::firstOrCreate([
            'struttura_id' => $struttura->id
        ]);

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
}
