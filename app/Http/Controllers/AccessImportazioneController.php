<?php

namespace App\Http\Controllers;

use App\Models\Schedina;
use App\Models\Struttura;
use App\Services\AccessDatiSchedina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AccessImportazioneController extends SchedinaController
{
    public function profiloAccess(Request $request)
    {
        $struttura = $request->attributes->get('access_struttura');
        return response()->json([
            'struttura_id' => (int) $struttura->id,
            'nome_struttura' => $struttura->nome_struttura,
            'proprietario_id' => (int) $struttura->proprietario_id,
        ]);
    }

    public function storeAccess(Request $request)
    {
        $source = $request->validate([
            'versione' => ['required', 'in:1'],
            'origine' => ['nullable', 'in:documento,checkin'],
            'richiesta_id' => ['required', 'uuid'], 'codice_checkin' => ['required', 'string', 'max:40'],
            'confermato_il' => ['required', 'date'],
            'prenotazione' => ['required', 'array'],
            'prenotazione.riferimento' => ['nullable', 'string', 'max:80'],
            'prenotazione.email_ospite' => ['nullable', 'email', 'max:191'],
            'prenotazione.telefono_ospite' => ['nullable', 'string', 'max:50'],
            'prenotazione.numero_ospiti' => ['required', 'integer', 'min:1', 'max:99'],
            'prenotazione.camere_occupate' => ['nullable', 'integer', 'min:1', 'max:99'],
            'prenotazione.letti_occupati' => ['nullable', 'integer', 'min:1', 'max:99'],
            'titolare' => ['required', 'array'], 'titolare.tipo_documento' => ['required', 'in:carta_identita_elettronica,passaporto,patente_guida'],
            'titolare.dati' => ['required', 'array'],
            'titolare.dati.nome' => ['required', 'string', 'max:100'], 'titolare.dati.cognome' => ['required', 'string', 'max:100'],
            'titolare.dati.sesso' => ['required', 'in:M,F'],
            'gruppo' => ['required', 'array'], 'gruppo.tipo_alloggiato' => ['required', 'string', 'in:16,17,18'],
            'gruppo.data_arrivo' => ['required', 'date_format:Y-m-d'], 'gruppo.data_partenza' => ['required', 'date_format:Y-m-d', 'after_or_equal:gruppo.data_arrivo'],
            'gruppo.componenti' => ['present', 'array', 'max:98'],
            'gruppo.componenti.*.id' => ['required', 'uuid', 'distinct'],
            'gruppo.componenti.*.tipo_alloggiato' => ['required', 'string', 'in:19,20'],
            'gruppo.componenti.*.dati' => ['required', 'array'],
            'gruppo.componenti.*.dati.nome' => ['required', 'string', 'max:100'],
            'gruppo.componenti.*.dati.cognome' => ['required', 'string', 'max:100'],
            'gruppo.componenti.*.dati.sesso' => ['required', 'in:M,F'],
        ]);
        $source = $request->only(['versione', 'origine', 'richiesta_id', 'codice_checkin', 'confermato_il', 'prenotazione', 'titolare', 'gruppo']);
        // Solo valori testuali, mai oggetti, ID locali, immagini o campi di controllo del gestionale.
        foreach ([$source['titolare']['dati'], ...array_column($source['gruppo']['componenti'], 'dati')] as $row) {
            foreach ($row as $value) abort_unless($value === null || (is_string($value) && mb_strlen($value) <= 250), 422);
        }
        $sid = (int) $request->attributes->get('access_struttura')->id;
        $connection = $request->attributes->get('access_connessione');
        $hash = hash('sha256', $request->getContent());
        [$result, $created] = DB::transaction(function () use ($request, $source, $sid, $connection, $hash) {
            Struttura::whereKey($sid)->lockForUpdate()->firstOrFail();
            $old = DB::table('access_importazioni')->where('struttura_id', $sid)->where('richiesta_id', $source['richiesta_id'])->first();
            if ($old) {
                abort_unless($old->connessione === $connection && hash_equals($old->impronta, $hash), 409, 'Check-in già importato: nessuna sovrascrittura automatica.');
                $sheet = Schedina::withoutGlobalScopes()->where('struttura_id', $sid)->find($old->schedina_id);
                abort_unless($sheet, 409, 'La schedina importata non è più disponibile.');
                return [$this->risposta($sheet, json_decode($old->avvisi, true)), false];
            }
            [$data, $warnings] = app(AccessDatiSchedina::class)->converti($source);
            $input = Request::create('/', 'POST', $data);
            $this->validateSchedinaRequest($input, false, $sid);
            $payload = $this->buildSchedinaPayload($input);
            $sheet = Schedina::create([...$payload, 'struttura_id' => $sid, 'circuito' => 'bozza', 'is_arrive' => false,
                'scheda' => $this->nextSchedaCode($sid, 'bozza'), 'customer_id' => null]);
            $this->syncComponenti($sheet, $input);
            DB::table('access_importazioni')->insert([
                'struttura_id' => $sid, 'connessione' => $connection, 'richiesta_id' => $source['richiesta_id'],
                'codice_checkin' => $source['codice_checkin'], 'schedina_id' => $sheet->id, 'impronta' => $hash,
                'avvisi' => json_encode($warnings), 'created_at' => now(), 'updated_at' => now(),
            ]);
            return [$this->risposta($sheet, $warnings), true];
        });
        return response()->json($result, $created ? 201 : 200);
    }

    private function risposta(Schedina $sheet, array $warnings): array
    {
        return ['schedina_id' => $sheet->id, 'numero' => $sheet->scheda, 'stato' => $sheet->circuito,
            'struttura_id' => $sheet->struttura_id, 'avvisi' => $warnings];
    }
}
