<?php

namespace App\Http\Controllers;

use App\Models\Struttura;
use App\Services\PresenzeReportService;
use App\Support\StrutturaCorrente;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PresenzeController extends Controller
{
    public function __construct(private readonly PresenzeReportService $service)
    {
    }

    public function index(Request $request)
    {
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

        if ($meseA < $meseDa) {
            [$meseDa, $meseA] = [$meseA, $meseDa];
        }

        if ($al->lt($dal)) {
            [$dal, $al] = [$al->copy(), $dal->copy()];
        }

        $riepilogo = $this->service->riepilogoAnno($struttura->id, $anno)
            ->filter(fn ($row) => $row['mese'] >= $meseDa && $row['mese'] <= $meseA)
            ->values();
        $dettaglio = $this->filterDettaglioByCategoria(
            $this->service->dettaglioPeriodo($struttura->id, $dal, $al),
            $categoria,
            $dal,
            $al
        );
        $oggi = $this->service->situazioneOggi($struttura->id, $giornoSituazione);
        $movimenti = $this->service->movimentiPeriodo($struttura->id, $dal, $al);
        [$occupazioneDal, $occupazioneAl] = $this->resolveOccupazionePeriodo($occupazioneAnno, $occupazioneMese);
        $occupazione = $this->service->occupazionePeriodo(
            $struttura->id,
            $occupazioneDal,
            $occupazioneAl,
            (int) ($struttura->camere_disponibili ?? 0),
            (int) ($struttura->letti_disponibili ?? 0)
        );

        return view('statistica.presenze.index', [
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
        $struttura = $this->resolveStruttura($request);
        $anno = max(2020, (int) $request->input('anno', now()->year));
        $meseDa = min(12, max(1, (int) $request->input('mese_da', 1)));
        $meseA = min(12, max(1, (int) $request->input('mese_a', 12)));
        if ($meseA < $meseDa) {
            [$meseDa, $meseA] = [$meseA, $meseDa];
        }

        $riepilogo = $this->service->riepilogoAnno($struttura->id, $anno)
            ->filter(fn ($row) => $row['mese'] >= $meseDa && $row['mese'] <= $meseA)
            ->values();

        return view('statistica.presenze.print-riepilogo', compact('struttura', 'anno', 'meseDa', 'meseA', 'riepilogo'));
    }

    public function printDettaglio(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        $categoria = (string) $request->input('categoria', 'tutte');
        $dal = Carbon::parse($request->input('dal', now()->startOfMonth()->toDateString()))->startOfDay();
        $al = Carbon::parse($request->input('al', now()->endOfMonth()->toDateString()))->startOfDay();
        if ($al->lt($dal)) {
            [$dal, $al] = [$al->copy(), $dal->copy()];
        }

        $dettaglio = $this->filterDettaglioByCategoria(
            $this->service->dettaglioPeriodo($struttura->id, $dal, $al),
            $categoria,
            $dal,
            $al
        );

        return view('statistica.presenze.print-dettaglio', compact('struttura', 'dal', 'al', 'categoria', 'dettaglio'));
    }

    private function resolveStruttura(Request $request): Struttura
    {
        $id = StrutturaCorrente::getId() ?? $request->user()?->struttura_id;
        abort_unless($id, 403, 'Struttura non selezionata.');

        return Struttura::query()->findOrFail($id);
    }

    private function resolveOccupazionePeriodo(int $anno, string $mese): array
    {
        if ($mese === 'tutto') {
            $dal = Carbon::create($anno, 1, 1)->startOfYear();
            $al = $dal->copy()->endOfYear();

            return [$dal, $al];
        }

        $meseInt = min(12, max(1, (int) $mese));
        $dal = Carbon::create($anno, $meseInt, 1)->startOfMonth();
        $al = $dal->copy()->endOfMonth();

        return [$dal, $al];
    }

    private function filterDettaglioByCategoria(array $dettaglio, string $categoria, Carbon $dal, Carbon $al): array
    {
        $categoria = in_array($categoria, ['tutte', 'italiane', 'straniere'], true) ? $categoria : 'tutte';
        $rows = collect($dettaglio['rows'] ?? []);

        if ($categoria === 'italiane') {
            $rows = $rows->where('categoria', 'Italiane')->values();
        } elseif ($categoria === 'straniere') {
            $rows = $rows->where('categoria', 'Straniere')->values();
        }

        $dettaglio['rows'] = $rows;
        $dettaglio['totali'] = [
            'schedine' => $rows->count(),
            'arrivi' => (int) $rows->sum('persone'),
            'partenze' => (int) $rows->filter(fn ($row) => $row['partenza'] && $row['partenza']->betweenIncluded($dal, $al))->sum('persone'),
            'presenze' => (int) $rows->sum('presenze'),
            'adulti' => (int) $rows->sum('adulti'),
            'minori' => (int) $rows->sum('minori'),
            'italiani' => (int) $rows->where('categoria', 'Italiane')->sum('presenze'),
            'stranieri' => (int) $rows->where('categoria', 'Straniere')->sum('presenze'),
        ];

        return $dettaglio;
    }
}
