<?php

namespace App\Services;

use App\Models\QuesturaTransmission;
use App\Models\Struttura;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class QuesturaTrasmissioni
{
    public function __construct(
        private QuesturaTxtExportService $service,
        private QuesturaWebService $webService,
    ) {
    }

    public function verificaEInvia(Struttura $struttura, ?int $userId, Carbon $giorno, string $txt, Collection $analisi): array
    {
        [$verifica, $esito] = $this->esegui($struttura, $userId, 'verify', $giorno, $giorno, $txt, $analisi, 'schedina');
        $righe = (int) $verifica->righe_count;
        if ($verifica->status !== 'simulation' && ($verifica->status !== 'unknown'
            || ($esito['valid_rows'] ?? null) !== $righe || ($esito['row_errors'] ?? null) !== [])) {
            throw ValidationException::withMessages(['questura_ws' => 'Verifica Questura non riuscita. Controlla lo storico e correggi i dati prima di inviare.']);
        }

        $campiCredenziali = array_flip(['questura_username', 'questura_password', 'questura_wskey']);
        $credenzialiVerificate = array_intersect_key($struttura->getRawOriginal(), $campiCredenziali);
        $struttura->refresh();
        $schedinaId = $analisi->sole()['schedina']->id;
        $schedina = $this->service->schedinaDelGiorno($struttura->id, $giorno, $schedinaId);
        if (!$schedina || $credenzialiVerificate !== array_intersect_key($struttura->getRawOriginal(), $campiCredenziali)
            || ($userId === null && ($struttura->questura_modalita !== 'automatico'
            || !$struttura->servizioAttivo() || $schedina->questura_export_count > 0))) {
            throw ValidationException::withMessages(['questura_ws' => 'La schedina o la modalità d’invio è cambiata. Verifica lo stato in Questura.']);
        }

        $corrente = $this->service->analizzaSchedine(collect([$schedina]));

        return $this->esegui($struttura, $userId, 'send', $giorno, $giorno, $this->service->buildTxt($corrente), $corrente, 'schedina');
    }

    public function esegui(Struttura $struttura, ?int $userId, string $mode, Carbon $dal, Carbon $al, string $txt, Collection $analisi, string $scope = 'periodo'): array
    {
        $this->webService->assertTransportEnabled($struttura);
        $credStatus = $this->webService->credentialsStatus($struttura);
        if (!$credStatus['configured']) {
            throw ValidationException::withMessages(['questura_ws' => 'Credenziali Questura incomplete.']);
        }
        $questuraContext = app(QuesturaProfilo::class)->context($struttura);
        $schedinaIds = $analisi->pluck('schedina.id')->filter()->values()->all();
        $filename = $this->service->filename($dal, $al);
        $payload = [
            'dal' => $dal->toDateString(),
            'al' => $al->toDateString(),
            'filename' => $filename,
            'schedina_ids' => $schedinaIds,
            'txt_base64' => base64_encode($txt),
            'transport_mode' => $credStatus['simulation'] ? 'simulation' : 'live',
            'questura_context' => $questuraContext,
            'sha256' => hash('sha256', $txt),
            'byte_size' => strlen($txt),
            'charset' => 'UTF-8',
            'component_ids' => $analisi->pluck('schedina')->flatMap(fn ($s) => $s->componenti->pluck('id'))->values()->all(),
        ];

        $result = EsitoTrasmissioneQuestura::crea('in_progress', $mode === 'send' ? 'send' : 'test');

        try {
            $transmission = DB::transaction(function () use ($struttura, $userId, $mode, $dal, $al, $schedinaIds, $txt, $payload, $result, $scope) {
                if ($mode === 'send') {
                    $this->requireVerifiedPayload($struttura->id, $dal, $al, $txt, $payload, $scope);
                }
                if ($mode === 'send' && $payload['transport_mode'] === 'live') {
                    // Gli archivi precedenti senza identità non diventano reinviabili
                    // dopo la migration, neppure se il payload è già minimizzato.
                    if (QuesturaTransmission::where('struttura_id', $struttura->id)
                        ->where('mode', 'send')->whereNull('identity_reserved_at')
                        ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'simulation'))->exists()) {
                        throw ValidationException::withMessages(['questura_ws' => 'Invii precedenti senza riserva delle Schedine: occorre riconciliare lo storico prima di un nuovo Send.']);
                    }
                }
                $transmission = $this->storeTransmission(
                    strutturaId: $struttura->id,
                    userId: $userId,
                    mode: $mode,
                    dal: $dal,
                    al: $al,
                    schedinaIds: $schedinaIds,
                    righeCount: substr_count($txt, "\r\n") + ($txt !== '' ? 1 : 0),
                    payload: $payload,
                    result: $result,
                    scope: $scope,
                );

                if ($mode === 'send') {
                    $ids = array_values(array_unique($schedinaIds));
                    sort($ids, SORT_NUMERIC);
                    DB::table('questura_send_reservations')->insert(array_map(fn ($id) => [
                        'struttura_id' => $struttura->id,
                        'schedina_id' => $id,
                        'transport_mode' => $payload['transport_mode'],
                        'questura_transmission_id' => $transmission->id,
                        'created_at' => now(),
                    ], $ids));
                }

                DB::table('questura_transmission_events')->insert(['questura_transmission_id' => $transmission->id, 'status' => $result['state'], 'result' => json_encode($result), 'created_at' => now()]);
                return $transmission;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['questura_ws' => 'Una Schedina o il payload sono già riservati per invio: verificare lo storico senza ripetere Send.']);
        }
        try {
            $result = $mode === 'verify'
                ? $this->webService->verify($struttura, $txt)
                : $this->webService->send($struttura, $txt);
        } catch (Throwable) {
            $result = EsitoTrasmissioneQuestura::crea($mode === 'send' ? 'uncertain' : 'technical_error', $mode === 'send' ? 'send' : 'test');
        }
        $result = EsitoTrasmissioneQuestura::sanifica($result);
        DB::transaction(function () use ($transmission, $result) {
            $transmission->update(['status' => $result['state'], 'result' => $result, 'response_message' => $result['message']]);
            DB::table('questura_transmission_events')->insert(['questura_transmission_id' => $transmission->id, 'status' => $result['state'], 'result' => json_encode($result), 'created_at' => now()]);
            if ($transmission->mode === 'send' && ($result['transmission_excluded'] ?? false) === true) {
                // Solo una prova positiva del trasporto escluso libera il retry.
                // Crash prima di questo commit: resta in_progress e bloccato.
                DB::table('questura_send_reservations')->where('questura_transmission_id', $transmission->id)->delete();
                DB::table('questura_transmissions')->where('id', $transmission->id)->update(['send_key' => null]);
            }
        });

        return [$transmission, $result];
    }

    private function requireVerifiedPayload(int $strutturaId, Carbon $dal, Carbon $al, string $txt, array $payload, string $scope): void
    {
        // La verifica più recente del contesto prevale anche se fallita o minimizzata.
        // Nessun ID/hash fornito dal browser autorizza il trasporto.
        $verify = QuesturaTransmission::where('struttura_id', $strutturaId)
            ->where('mode', 'verify')->whereDate('dal', $dal)->whereDate('al', $al)
            ->where('payload->transport_mode', $payload['transport_mode'])
            ->where('scope_type', $scope)
            ->when($scope === 'schedina', fn ($query) => $query->whereJsonContains('schedina_ids', $payload['schedina_ids'][0]))
            ->latest('id')->lockForUpdate()->first();
        $rows = substr_count($txt, "\r\n") + ($txt !== '' ? 1 : 0);
        $positive = $verify && ($payload['transport_mode'] === 'simulation'
            ? $verify->status === 'simulation'
            : ($verify->status === 'unknown'
                && ($verify->result['valid_rows'] ?? null) === $rows
                && ($verify->result['row_errors'] ?? null) === []));
        $bytes = $verify ? base64_decode($verify->payload['txt_base64'] ?? '', true) : false;
        $verifiedContext = $verify?->questura_context ?? ['profilo' => 'standard'];
        $currentContext = $payload['questura_context'];
        // Un oggetto JSON può essere riletto con chiavi in ordine diverso.
        // Normalizzare solo l'ordine conserva il confronto rigoroso di chiavi e valori.
        ksort($verifiedContext);
        ksort($currentContext);
        if (!$positive || $verify->finalized_at || $verify->payload_deleted_at
            || $verify->scope_type !== $scope
            || $verifiedContext !== $currentContext
            || $verify->schedina_ids !== $payload['schedina_ids']
            || $verify->component_ids !== $payload['component_ids']
            || (int) $verify->righe_count !== $rows
            || (int) $verify->byte_size !== strlen($txt)
            || $verify->charset !== 'UTF-8'
            || !is_string($verify->sha256) || !hash_equals($verify->sha256, $payload['sha256'])
            || $bytes !== $txt) {
            throw ValidationException::withMessages(['questura_ws' => 'WS Test positivo assente o non più valido per questo elenco: i dati possono essere cambiati dopo il test. Ripetere WS Test prima di Send.']);
        }
    }

    private function storeTransmission(
        int $strutturaId,
        ?int $userId,
        string $mode,
        Carbon $dal,
        Carbon $al,
        array $schedinaIds,
        int $righeCount,
        array $payload,
        array $result,
        string $scope,
    ): QuesturaTransmission {
        $result = EsitoTrasmissioneQuestura::sanifica($result);
        $sourceIds = array_values(array_unique($schedinaIds));
        sort($sourceIds, SORT_NUMERIC);
        return QuesturaTransmission::query()->create([
            'struttura_id' => $strutturaId,
            'user_id' => $userId,
            'questura_export_id' => null,
            'mode' => $mode,
            'scope_type' => $scope,
            'dal' => $dal->toDateString(),
            'al' => $al->toDateString(),
            'schedina_ids' => $schedinaIds,
            'schedine_count' => count($schedinaIds),
            'righe_count' => $righeCount,
            'status' => $result['state'],
            'response_code' => null,
            'response_message' => $result['message'],
            'response_detail' => null,
            'payload' => $payload,
            'questura_context' => $payload['questura_context'] ?? null,
            'sha256' => $payload['sha256'] ?? null,
            'byte_size' => $payload['byte_size'] ?? null,
            'charset' => $payload['charset'] ?? null,
            'component_ids' => $payload['component_ids'] ?? null,
            // L'hash resta controllo dei byte; l'identità include le sorgenti PMS.
            'send_key' => $mode === 'send' ? hash('sha256', ($payload['transport_mode'] ?? 'live') . ':' . $strutturaId . ':' . implode(',', $sourceIds) . ':' . ($payload['sha256'] ?? '')) : null,
            'identity_reserved_at' => $mode === 'send' ? now() : null,
            'result' => $result,
            'executed_at' => now(),
        ]);
    }

}
