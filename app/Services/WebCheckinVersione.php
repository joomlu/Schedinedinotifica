<?php

namespace App\Services;

use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;

class WebCheckinVersione
{
    public function contesto(WebCheckinRichiesta $request): string
    {
        return hash_hmac('sha256', 'web-checkin:'.$request->struttura_id.':'.$request->id, (string) config('app.key'));
    }

    public function of(WebCheckinRichiesta $request): string
    {
        $sheet = Schedina::withoutGlobalScopes()->where('struttura_id', $request->struttura_id)->findOrFail($request->schedina_id);
        $people = Componenti::withoutGlobalScopes()->where('struttura_id', $request->struttura_id)->where('schedina_id', $sheet->id)->orderBy('id')->get()->map->getAttributes()->all();

        return hash_hmac('sha256', json_encode([
            $request->only(['arrivo', 'partenza', 'nome_referente', 'email', 'whatsapp', 'quantita_persone', 'stato', 'note', 'link_revoked_at', 'link_expires_at']),
            $sheet->getAttributes(), $people,
        ], JSON_THROW_ON_ERROR), (string) config('app.key'));
    }
}
