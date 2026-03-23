<?php

namespace App\Http\Controllers;

use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Services\TassaDiSoggiornoService;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TassaReportController extends Controller
{
    private TassaDiSoggiornoService $service;

    public function __construct(TassaDiSoggiornoService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $mese = (int) $request->input('mese', now()->month);
        $anno = (int) $request->input('anno', now()->year);
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 10;
        $missingSchedina = !Schema::hasTable('schedina');

        $strutturaId = StrutturaCorrente::getId() ?? $request->user()->struttura_id;
        if (!$strutturaId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $struttura = Struttura::findOrFail($strutturaId);
        $config = TassaDiSoggiorno::where('struttura_id', $strutturaId)->first();
        $esenzioni = Schema::hasTable('tassa_esenzioni')
            ? TassaEsenzione::where('struttura_id', $strutturaId)->where('attivo', true)->orderBy('ordine')->orderBy('codice')->get()
            : collect();

        $righe = $missingSchedina ? [] : $this->buildRows($mese, $anno, $strutturaId, $struttura, $config, $esenzioni);
        $collection = collect($righe);
        $paginator = new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('tassa_di_soggiorno.rapporto', [
            'righe' => $paginator,
            'mese' => $mese,
            'anno' => $anno,
            'config' => $config,
            'struttura' => $struttura,
            'missingSchedina' => $missingSchedina,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $mese = (int) $request->input('mese', now()->month);
        $anno = (int) $request->input('anno', now()->year);

        $strutturaId = StrutturaCorrente::getId() ?? $request->user()->struttura_id;
        if (!$strutturaId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        if (!Schema::hasTable('schedina')) {
            return redirect()->route('tassa_di_soggiorno.rapporto', ['mese' => $mese, 'anno' => $anno])
                ->withErrors(['schedina' => 'Tabella schedina mancante: esegui le migrazioni o importa il dump iniziale.']);
        }

        $config = TassaDiSoggiorno::where('struttura_id', $strutturaId)->first();
        $esenzioni = Schema::hasTable('tassa_esenzioni')
            ? TassaEsenzione::where('struttura_id', $strutturaId)->where('attivo', true)->orderBy('ordine')->orderBy('codice')->get()
            : collect();

        $struttura = Struttura::findOrFail($strutturaId);
        $righe = $this->buildRows($mese, $anno, $strutturaId, $struttura, $config, $esenzioni);
        $lines = [];
        $lines[] = 'data_inizio;data_fine;tipo;data_reg;arrivo;partenza;nominativo;soggetti;pernottamenti_imponibili;tariffa';
        foreach ($righe as $riga) {
            $lines[] = implode(';', [
                $riga['arrivo'],
                $riga['partenza'],
                $riga['tipo'],
                $riga['data_reg'],
                $riga['arrivo'],
                $riga['partenza'],
                str_replace(';', ',', $riga['nominativo']),
                $riga['soggetti'],
                $riga['pernottamenti_imponibili'],
                $riga['tariffa'],
            ]);
        }

        $csv = implode("\n", $lines);
        $monthName = now()->setMonth($mese)->locale('it')->monthName;
        $filename = sprintf('%s_%d.csv', str_replace(' ', '_', strtolower($monthName)), $anno);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function buildRows(int $mese, int $anno, int $strutturaId, Struttura $struttura, ?TassaDiSoggiorno $config, Collection $esenzioni): array
    {
        if (!Schema::hasTable('schedina')) {
            return [];
        }

        $schedine = Schedina::where('is_arrive', 0)
            ->where('struttura_id', $strutturaId)
            ->get();
        $righe = [];

        foreach ($schedine as $schedina) {
            $arrivo = $this->service->parseDate($schedina->arrive);
            if (!$arrivo || $arrivo->month !== $mese || $arrivo->year !== $anno) {
                continue;
            }
            $partenza = $this->service->parseDate($schedina->departure);
            $componenti = Componenti::where('schedina_id', $schedina->id)->get();
            $dettaglio = $this->service->dettaglioSchedina($schedina, $componenti, $config, $esenzioni, $struttura);

            foreach ($dettaglio['righe'] as $riga) {
                $righe[] = [
                    'arrivo' => $arrivo?->toDateString(),
                    'partenza' => $partenza?->toDateString(),
                    'scheda' => $schedina->scheda,
                    'nominativo' => $riga['nome'],
                    'eta' => $riga['eta'],
                    'esente' => $riga['esente'],
                    'motivo' => $riga['motivo'],
                    'pernottamenti_imponibili' => $riga['notti_imponibili'],
                    'pernottamenti_oltre_max' => $riga['notti_oltre_max'],
                    'tassa' => $riga['subtotale'],
                    'tariffa' => $riga['aliquota'],
                    'tipo' => $riga['codice'] ?? 0,
                    'data_reg' => now()->toDateString(),
                    'soggetti' => 1,
                ];

                if ($riga['notti_oltre_max'] > 0) {
                    $righe[] = [
                        'arrivo' => $arrivo?->toDateString(),
                        'partenza' => $partenza?->toDateString(),
                        'scheda' => $schedina->scheda,
                        'nominativo' => $riga['nome'],
                        'eta' => $riga['eta'],
                        'esente' => $riga['esente'],
                        'motivo' => 'Oltre giorni max',
                        'pernottamenti_imponibili' => $riga['notti_oltre_max'],
                        'pernottamenti_oltre_max' => $riga['notti_oltre_max'],
                        'tassa' => 0,
                        'tariffa' => 0,
                        'tipo' => 777,
                        'data_reg' => now()->toDateString(),
                        'soggetti' => 1,
                    ];
                }
            }
        }

        return $righe;
    }
}
