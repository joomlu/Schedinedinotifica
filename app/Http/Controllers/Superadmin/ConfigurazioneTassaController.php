<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Struttura;
use App\Services\TassaConfig\GestioneProfiliTassa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ConfigurazioneTassaController extends Controller
{
    public function __construct(private GestioneProfiliTassa $gestione, private \App\Services\TassaConfig\RichiesteVerificaTassa $richieste) {}

    private function contesto(Request $request): array
    {
        abort_unless($request->user()?->ruolo === 'super_admin', 403);
        $v = $request->validate(['comune' => 'required|string|max:120', 'anno' => 'required|integer|min:2015|max:2100', 'categoria' => 'nullable|integer|min:1|max:5'], [
            'comune.required' => 'Indicare il Comune.', 'anno.required' => 'Indicare l’anno fiscale.', 'anno.integer' => 'L’anno fiscale deve essere un numero intero.', 'anno.min' => 'L’anno deve essere almeno 2015.', 'anno.max' => 'L’anno non può superare 2100.',
        ]);
        $v['comune'] = trim($v['comune']);
        if (in_array(mb_strtolower($v['comune']), ['bellaria-igea marina', 'bellaria igea marina'], true)) {
            $v['comune'] = 'Bellaria-Igea Marina';
        }

        return [$v['comune'], (int) $v['anno'], (int) ($v['categoria'] ?? 3)];
    }

    public function index(Request $request)
    {
        $request->merge(['comune' => $request->input('comune', 'Bellaria-Igea Marina'), 'anno' => $request->input('anno', now()->year)]);
        [$comune, $anno, $categoria] = $this->contesto($request);
        $scheda = $this->gestione->archivio->leggi($this->gestione->archivio->scheda($comune, $anno));
        $profili = $comune === 'Bellaria-Igea Marina' ? $this->gestione->archivio->profiliBellaria() : [];
        $profilo = collect($profili)->first(fn ($p) => substr($p['valida_dal'], 0, 4) <= $anno && substr($p['valida_al'], 0, 4) >= $anno);
        $colonnaComune = \Illuminate\Support\Facades\Schema::hasColumn('struttura', 'citta') ? 'citta' : 'città';
        $comuni = Struttura::withoutGlobalScopes()->distinct()->orderBy($colonnaComune)->pluck($colonnaComune)->filter()->push('Bellaria-Igea Marina')->unique()->values();
        $inCoda = $this->richieste->inCoda($comune, $anno);
        if ($request->wantsJson()) {
            return response()->json(['in_coda' => $inCoda, 'ultima_verifica' => $scheda['ultima_verifica']]);
        }

        return view('superadmin.tassa.configurazione', compact('comune', 'anno', 'categoria', 'scheda', 'profilo', 'comuni', 'inCoda'));
    }

    public function verifica(Request $request)
    {
        [$comune, $anno] = $this->contesto($request);
        $this->richieste->richiedi($comune, $anno, $request->user()->id);

        return back()->with('status', 'Verifica richiesta. Il controllo dedicato aggiornerà questa scheda; i risultati non sono ancora disponibili.');
    }

    public function documento(Request $request)
    {
        [$comune, $anno] = $this->contesto($request);
        $request->validate(['pdf' => 'nullable|file|mimes:pdf|max:8192', 'testo' => 'nullable|string|max:100000', 'fonte' => 'nullable|url:https|max:2000'], ['pdf.mimes' => 'Caricare un PDF valido.', 'pdf.max' => 'Il PDF non può superare 8 MB.', 'fonte.url' => 'Indicare un collegamento HTTPS valido.']);
        if (! $request->hasFile('pdf') && ! filled($request->testo) && ! filled($request->fonte)) {
            throw ValidationException::withMessages(['documento' => 'Caricare un PDF, incollare un testo oppure indicare un collegamento ufficiale.']);
        }
        $originale = null;
        if ($request->hasFile('pdf') || filled($request->testo)) {
            $originale = $this->gestione->conserva($comune, $anno, $request->hasFile('pdf') ? $request->file('pdf')->get() : $request->testo, $request->hasFile('pdf') ? 'pdf' : 'testo', $request->fonte, false, $request->hasFile('pdf') ? $request->file('pdf')->getClientOriginalName() : 'Testo fornito dal Superadmin');
        }
        if (filled($request->fonte)) {
            $this->richieste->richiedi($comune, $anno, $request->user()->id, $request->fonte, $originale['id'] ?? null);

            return back()->with('status', 'Verifica della fonte richiesta. L’originale caricato è conservato, la provenienza sarà controllata dal processo dedicato.');
        }

        return back()->with('status', 'Documento conservato e analizzato. Verificare i riferimenti e le eventuali informazioni mancanti.');
    }

    public function conferma(Request $request)
    {
        [$comune, $anno] = $this->contesto($request);
        $rules = ['documento' => 'required|regex:/^[a-f0-9]{64}$/', 'conferma' => 'accepted', 'motivazione' => 'required|string|min:20|max:2000'];
        foreach (['valida_dal', 'valida_al', 'inizio', 'fine'] as $campo) {
            $rules[$campo] = 'required|date_format:Y-m-d';
        }
        foreach (['giorni_massimo', 'max_age_children', 'min_age_adult'] as $campo) {
            $rules[$campo] = 'required|integer|min:1|max:365';
        }
        foreach (range(1, 5) as $c) {
            $rules['tariffa_'.$c] = 'required|numeric|min:0|decimal:0,2';
        }
        $attributiTariffe = [];
        foreach (range(1, 5) as $c) {
            $attributiTariffe['tariffa_'.$c] = 'tariffa alberghi '.$c.' stelle';
        }
        $v = $request->validate($rules, ['decimal' => 'Il campo :attribute deve avere al massimo due cifre decimali.', 'required' => 'Completare il campo :attribute e il riferimento al documento.', 'date_format' => 'Il campo :attribute deve contenere una data valida.', 'accepted' => 'Confermare la verifica di provenienza, validità e delle esenzioni.'], [
            'documento' => 'documento ufficiale', 'motivazione' => 'motivazione della verifica', 'valida_dal' => 'inizio validità', 'valida_al' => 'fine validità', 'inizio' => 'inizio periodo', 'fine' => 'fine periodo', 'giorni_massimo' => 'limite notti', 'max_age_children' => 'età massima esenzione', 'min_age_adult' => 'età minima imponibile',
        ] + $attributiTariffe);
        $s = $this->gestione->archivio->leggi($this->gestione->archivio->scheda($comune, $anno));
        abort_unless(isset($s['documenti'][$v['documento']]), 404);
        $d = $s['documenti'][$v['documento']];
        if (! $d['ufficiale']) {
            $attestazione = $request->validate([
                'verifica_provenienza' => 'accepted',
                'riscontro_comunale' => 'required|string|min:30|max:2000',
                'riferimento_vigore' => 'required|string|min:20|max:2000',
            ], ['accepted' => 'Occorre attestare una verifica della provenienza separata dal caricamento.', 'required' => 'Indicare :attribute.', 'min' => 'Descrivere in modo concreto :attribute.'], ['riscontro_comunale' => 'il protocollo, la firma verificata o il riscontro dell’Ufficio Tributi', 'riferimento_vigore' => 'l’articolo o la comunicazione che attesta la vigenza nell’anno selezionato']);
            $d['ufficiale'] = true;
            $d['attestazione_superadmin'] = ['autore' => $request->user()->id, 'data' => now()->toIso8601String()] + $attestazione;
        }
        $d['revisione'] = ['superadmin' => $request->user()->id, 'motivazione' => $v['motivazione'], 'data' => now()->toIso8601String()];
        $this->gestione->applica($comune, $anno, $d, $v, 'confermata dal Superadmin');
        $s = $this->gestione->archivio->leggi($this->gestione->archivio->scheda($comune, $anno));
        $s['documenti'][$d['id']] = $d;
        $this->gestione->archivio->scrivi($this->gestione->archivio->scheda($comune, $anno), $s);

        return back()->with('status', 'Nuovo profilo futuro registrato. Profili precedenti e storico conservati.');
    }

    public function download(Request $request, string $id)
    {
        [$comune, $anno] = $this->contesto($request);
        $s = $this->gestione->archivio->leggi($this->gestione->archivio->scheda($comune, $anno));
        abort_unless(isset($s['documenti'][$id]), 404);

        return Storage::disk('local')->download($s['documenti'][$id]['path'], 'documentazione-tassa.'.($s['documenti'][$id]['tipo'] === 'pdf' ? 'pdf' : 'txt'), ['X-Content-Type-Options' => 'nosniff']);
    }
}
