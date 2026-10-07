<?php

namespace App\Services;

use App\Models\IstatExport;
use App\Models\IstatTransmission;
use App\Models\Struttura;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class IstatOperationService
{
    public function reserve(Struttura $struttura, IstatExport $export, string $mode, ?int $userId): IstatTransmission
    {
        if ((int) $export->struttura_id !== (int) $struttura->id
            || ! preg_match('/^[a-f0-9]{64}$/D', (string) $export->sha256)
            || ! in_array($mode, ['send', 'verify', 'manual'], true)) {
            throw ValidationException::withMessages(['istat_ws' => 'Identità della copia XML non valida per questa struttura.']);
        }

        return DB::transaction(function () use ($struttura, $export, $mode, $userId) {
            Struttura::whereKey($struttura->id)->lockForUpdate()->firstOrFail();
            $key = hash('sha256', $struttura->id.'|'.$mode.'|'.$export->sha256);
            $prior = IstatTransmission::where('idempotency_key', $key)->first();
            while ($prior && $prior->reconciled_at) {
                $key = hash('sha256', $key.'|after:'.$prior->id);
                $prior = IstatTransmission::where('idempotency_key', $key)->first();
            }
            if ($prior && in_array($prior->status, ['disabled', 'not_delivered'], true)) {
                $prior->update(['status' => 'pending', 'attempts' => $prior->attempts + 1, 'executed_at' => null]);
            } elseif ($prior) {
                throw ValidationException::withMessages(['istat_ws' => 'Operazione già registrata #'.$prior->id.'. Consultare lo storico prima di ripetere.']);
            }
            // I tentativi legacy non hanno una prenotazione dimostrabile: blocco conservativo.
            if ($mode !== 'verify' && IstatTransmission::where('struttura_id', $struttura->id)
                ->where('mode', '!=', 'verify')->whereNull('reconciled_at')->whereNull('idempotency_key')
                ->whereDate('dal', '<=', $export->al)->whereDate('al', '>=', $export->dal)->exists()) {
                throw ValidationException::withMessages(['istat_ws' => 'Storico precedente da riconciliare sul portale Ross1000.']);
            }
            if ($mode !== 'verify' && DB::table('istat_communication_days')->where('struttura_id', $struttura->id)
                ->whereBetween('giorno', [$export->dal->toDateString(), $export->al->toDateString()])->exists()) {
                throw ValidationException::withMessages(['istat_ws' => 'Periodo già prenotato o comunicato. Verificare lo storico Ross1000; retry automatico bloccato.']);
            }
            if ($mode !== 'verify' && IstatTransmission::where('struttura_id', $struttura->id)
                ->where('mode', '!=', 'verify')->whereNull('reconciled_at')
                ->whereNotIn('status', ['not_delivered', 'disabled'])
                ->whereDate('dal', '>', $export->al)->exists()) {
                throw ValidationException::withMessages(['istat_ws' => 'Caricamento fuori ordine cronologico: verificare sul portale e riconciliare anche i periodi successivi prima di ripetere.']);
            }
            $tx = $prior ?? IstatTransmission::create([
                'struttura_id' => $struttura->id, 'user_id' => $userId, 'istat_export_id' => $export->id,
                'mode' => $mode, 'dal' => $export->dal, 'al' => $export->al,
                'schedina_ids' => $export->schedina_ids, 'schedine_count' => $export->schedine_count,
                'movimenti_count' => $export->movimenti_count, 'status' => 'pending', 'idempotency_key' => $key,
            ]);
            if ($mode !== 'verify') {
                for ($day = Carbon::parse($export->dal); $day->lte($export->al); $day->addDay()) {
                    DB::table('istat_communication_days')->insert([
                        'struttura_id' => $struttura->id, 'giorno' => $day->toDateString(), 'istat_transmission_id' => $tx->id,
                    ]);
                }
            }
            $this->event($tx, 'pending', $userId);

            return $tx;
        });
    }

    public function finalize(IstatTransmission $tx, array $result): void
    {
        $result = EsitoTrasmissioneIstat::sanifica($result);
        DB::transaction(function () use ($tx, $result) {
            $locked = IstatTransmission::whereKey($tx->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['istat_ws' => 'Operazione già finalizzata.']);
            }
            $locked->update([
                'status' => $result['state'], 'response_code' => $result['transport']['http_status'],
                'response_message' => $result['message'], 'response_detail' => null,
                'result' => $result, 'executed_at' => now(),
            ]);
            // Soltanto un errore certo prima del trasporto libera il periodo.
            if (in_array($result['state'], ['disabled', 'not_delivered'], true)) {
                DB::table('istat_communication_days')->where('istat_transmission_id', $tx->id)->delete();
            }
            $attempt = DB::table('istat_transmission_events')->where('struttura_id', $locked->struttura_id)
                ->where('istat_transmission_id', $locked->id)->where('status', 'pending')->latest('id')->first();
            $this->event($locked, $result['state'], $attempt ? $attempt->user_id : $locked->user_id);
        });
    }

    public function reconcile(IstatTransmission $tx, int $userId, string $procedure = 'verifica_portale'): void
    {
        if (! in_array($procedure, ['verifica_portale', 'correzione_portale', 'annullamento_portale', 'reimportazione_cronologica'], true)) {
            throw ValidationException::withMessages(['istat_ws' => 'Procedura sul portale non valida.']);
        }
        DB::transaction(function () use ($tx, $userId, $procedure) {
            Struttura::whereKey($tx->struttura_id)->lockForUpdate()->firstOrFail();
            $locked = IstatTransmission::whereKey($tx->id)->lockForUpdate()->firstOrFail();
            if ($locked->mode === 'verify' || $locked->reconciled_at
                || $locked->status === 'pending') {
                throw ValidationException::withMessages(['istat_ws' => 'Operazione non riconciliabile.']);
            }
            $locked->update(['reconciled_at' => now()]);
            DB::table('istat_communication_days')->where('istat_transmission_id', $tx->id)->delete();
            $this->event($locked, 'portal_reconciled', $userId, ['procedure' => $procedure]);
        });
    }

    private function event(IstatTransmission $tx, string $status, ?int $userId, ?array $details = null): void
    {
        DB::table('istat_transmission_events')->insert([
            'struttura_id' => $tx->struttura_id, 'istat_transmission_id' => $tx->id,
            'user_id' => $userId, 'status' => $status, 'created_at' => now(),
            'result' => $details === null ? null : json_encode($details, JSON_THROW_ON_ERROR),
        ]);
    }
}
