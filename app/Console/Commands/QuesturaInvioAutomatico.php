<?php

namespace App\Console\Commands;

use App\Models\Struttura;
use App\Services\QuesturaTrasmissioni;
use App\Services\QuesturaTxtExportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class QuesturaInvioAutomatico extends Command
{
    protected $signature = 'questura:invio-serale';

    protected $description = 'Elabora alle 23:00 le schedine del giorno per le strutture abilitate';

    public function handle(QuesturaTxtExportService $export, QuesturaTrasmissioni $trasmissioni): int
    {
        $ora = Carbon::now('Europe/Rome');
        if (!app()->environment('production') || $ora->format('H:i') !== '23:00') {
            return self::SUCCESS;
        }

        $giorno = $ora->copy()->startOfDay();
        $errori = 0;
        foreach (Struttura::query()->where('questura_modalita', 'automatico')->lazyById() as $struttura) {
            if (!Cache::add('questura:serale:'.$struttura->id.':'.$giorno->toDateString(), true, $ora->copy()->addDays(2))) {
                continue;
            }

            $avvisi = [];
            try {
                if (!$struttura->servizioAttivo()) {
                    throw new \RuntimeException('Servizio struttura sospeso o scaduto.');
                }

                $schedine = $export->schedinePerPeriodo($struttura->id, $giorno, $giorno);
                $riservate = DB::table('questura_send_reservations')
                    ->where('struttura_id', $struttura->id)->where('transport_mode', 'live')
                    ->whereIn('schedina_id', $schedine->pluck('id'))->pluck('schedina_id')->flip();

                foreach ($export->analizzaSchedine($schedine) as $item) {
                    $schedina = $item['schedina'];
                    if ($riservate->has($schedina->id) || $schedina->questura_send_count > 0) {
                        continue;
                    }
                    if (!$item['valida'] || $schedina->questura_export_count > 0) {
                        $avvisi[] = $schedina->id;
                        continue;
                    }

                    $struttura->refresh();
                    if ($struttura->questura_modalita !== 'automatico' || !$struttura->servizioAttivo()) {
                        break;
                    }

                    try {
                        $analisi = collect([$item]);
                        [, $esito] = $trasmissioni->verificaEInvia($struttura, null, $giorno, $export->buildTxt($analisi), $analisi);
                        if ($esito['state'] !== 'sent') {
                            $avvisi[] = $schedina->id;
                        }
                    } catch (Throwable) {
                        $avvisi[] = $schedina->id;
                    }
                }
            } catch (Throwable) {
                $avvisi[] = null;
            }

            if ($avvisi !== []) {
                $errori++;
                $chiave = 'questura:avviso-serale:'.$struttura->id;
                Cache::put($chiave, [
                    'giorno' => $giorno->format('d/m/Y'),
                    'schedine' => count(array_filter($avvisi)),
                    'errore' => in_array(null, $avvisi, true),
                ], $ora->copy()->addDays(7));
                Log::warning('Invio serale Questura da verificare', ['struttura_id' => $struttura->id, 'schedine' => count($avvisi)]);
            }
        }

        return $errori > 0 ? self::FAILURE : self::SUCCESS;
    }
}
