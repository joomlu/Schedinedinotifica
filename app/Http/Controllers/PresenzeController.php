<?php

namespace App\Http\Controllers;

use App\Models\Struttura;
use App\Services\PresenzeReportService;
use App\Support\StrutturaAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PresenzeController extends Controller
{
    public function __construct(private readonly PresenzeReportService $service)
    {
    }

    public function index(Request $request)
    {
        $this->validateFilters($request);
        $struttura = $this->resolveStruttura($request);
        $anno = max(2020, (int) $request->input('anno', now()->year));
        $meseDa = min(12, max(1, (int) $request->input('mese_da', 1)));
        $meseA = min(12, max(1, (int) $request->input('mese_a', 12)));
        $categoria = (string) $request->input('categoria', 'tutte');
        $occupazioneAnno = max(2020, (int) $request->input('occupazione_anno', now()->year));
        $occupazioneMese = (string) $request->input('occupazione_mese', (string) now()->month);
        $giornoSituazione = Carbon::parse($request->input('giorno_situazione', now()->toDateString()))->startOfDay();
        $dal = Carbon::parse($request->input('dal', now()->startOfMonth()->toDateString()))->startOfDay();
        $al = Carbon::parse($request->input('al', now()->endOfMonth()->toDateString()))->startOfDay();



        if ($request->input('tab', 'riepilogo') === 'riepilogo') {
            $dal = Carbon::create($anno, $meseDa, 1)->startOfDay();
            $al = Carbon::create($anno, $meseA, 1)->endOfMonth()->startOfDay();
            $giornoSituazione = $dal->copy();
            $occupazioneMese = 'periodo';
        } elseif (in_array($request->input('tab'), ['dettaglio', 'movimenti'], true)) {
            $giornoSituazione = $dal->copy();
            $occupazioneMese = 'periodo';
        }
        $riepilogo = $this->service->riepilogoAnno($struttura->id, $anno)
            ->filter(fn ($row) => $row['mese'] >= $meseDa && $row['mese'] <= $meseA)
            ->values();
        $dettaglio = $this->service->dettaglioPeriodo($struttura->id, $dal, $al, $categoria);
        $oggi = $this->service->situazioneOggi($struttura->id, $giornoSituazione);
        $movimenti = $this->service->movimentiPeriodo($struttura->id, $dal, $al);
        [$occupazioneDal, $occupazioneAl] = $occupazioneMese === 'periodo' ? [$dal, $al] : $this->resolveOccupazionePeriodo($occupazioneAnno, $occupazioneMese);
        $occupazione = $this->service->occupazionePeriodo(
            $struttura->id,
            $occupazioneDal,
            $occupazioneAl,
            (int) ($struttura->camere_disponibili ?? 0),
            (int) ($struttura->letti_disponibili ?? 0)
        );

        return view('statistica.presenze.index', [
            'avvisi' => $this->service->avvisi(),
            'struttura' => $struttura,
            'anno' => $anno,
            'meseDa' => $meseDa,
            'meseA' => $meseA,
            'categoria' => $categoria,
            'dal' => $dal,
            'al' => $al,
            'occupazioneAnno' => $occupazioneAnno,
            'occupazioneMese' => $occupazioneMese,
            'occupazioneDal' => $occupazioneDal,
            'occupazioneAl' => $occupazioneAl,
            'giornoSituazione' => $giornoSituazione,
            'riepilogo' => $riepilogo,
            'dettaglio' => $dettaglio,
            'oggi' => $oggi,
            'movimenti' => $movimenti,
            'occupazione' => $occupazione,
        ]);
    }

    public function printRiepilogo(Request $request)
    {
        $this->validateFilters($request);
        $struttura = $this->resolveStruttura($request);
        $anno = max(2020, (int) $request->input('anno', now()->year));
        $meseDa = min(12, max(1, (int) $request->input('mese_da', 1)));
        $meseA = min(12, max(1, (int) $request->input('mese_a', 12)));

        $riepilogo = $this->service->riepilogoAnno($struttura->id, $anno)
            ->filter(fn ($row) => $row['mese'] >= $meseDa && $row['mese'] <= $meseA)
            ->values();

        return view('statistica.presenze.print-riepilogo', compact('struttura', 'anno', 'meseDa', 'meseA', 'riepilogo'));
    }

    public function printDettaglio(Request $request)
    {
        $this->validateFilters($request);
        $struttura = $this->resolveStruttura($request);
        $categoria = (string) $request->input('categoria', 'tutte');
        $dal = Carbon::parse($request->input('dal', now()->startOfMonth()->toDateString()))->startOfDay();
        $al = Carbon::parse($request->input('al', now()->endOfMonth()->toDateString()))->startOfDay();

        $dettaglio = $this->service->dettaglioPeriodo($struttura->id, $dal, $al, $categoria);

        return view('statistica.presenze.print-dettaglio', compact('struttura', 'dal', 'al', 'categoria', 'dettaglio'));
    }

    private function resolveStruttura(Request $request): Struttura
    {
        return StrutturaAccess::resolve($request);
    }

    private function validateFilters(Request $request): void
    {
        $request->validate([
            'anno' => 'sometimes|integer|between:2020,2100',
            'mese_da' => 'sometimes|integer|between:1,12',
            'mese_a' => 'sometimes|integer|between:1,12',
            'dal' => 'sometimes|date_format:Y-m-d',
            'al' => 'sometimes|date_format:Y-m-d',
            'giorno_situazione' => 'sometimes|date_format:Y-m-d',
            'occupazione_anno' => 'sometimes|integer|between:2020,2100',
            'occupazione_mese' => 'sometimes|in:tutto,periodo,1,2,3,4,5,6,7,8,9,10,11,12',
            'categoria' => 'sometimes|in:tutte,italiane,straniere,sconosciute',
            'tab' => 'sometimes|in:riepilogo,dettaglio,oggi,movimenti,occupazione',
        ], [], ['dal' => 'data iniziale', 'al' => 'data finale', 'mese_a' => 'mese finale']);
        $dal = Carbon::parse($request->input('dal', now()->startOfMonth()->toDateString()))->startOfDay();
        $al = Carbon::parse($request->input('al', now()->endOfMonth()->toDateString()))->startOfDay();
        if ((int) $request->input('mese_da', 1) > (int) $request->input('mese_a', 12) || $al->lt($dal) || $dal->diffInDays($al) > 1095) {
            throw \Illuminate\Validation\ValidationException::withMessages(['al' => 'Seleziona un periodo ordinato di massimo tre anni.']);
        }
    }

    private function resolveOccupazionePeriodo(int $anno, string $mese): array
    {
        if ($mese === 'tutto') {
            $dal = Carbon::create($anno, 1, 1)->startOfYear();
            $al = $dal->copy()->endOfYear()->startOfDay();

            return [$dal, $al];
        }

        $meseInt = min(12, max(1, (int) $mese));
        $dal = Carbon::create($anno, $meseInt, 1)->startOfMonth();
        $al = $dal->copy()->endOfMonth()->startOfDay();

        return [$dal, $al];
    }

}
