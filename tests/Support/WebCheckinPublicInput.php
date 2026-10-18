<?php

namespace Tests\Support;

use App\Models\WebCheckinRichiesta;
use App\Services\WebCheckinLink;
use App\Services\WebCheckinVersione;

/** Prepara esplicitamente le versioni del modulo, come il GET dell’interfaccia. */
trait WebCheckinPublicInput
{
    private function webInput(string $uri, array $data): array
    {
        $path = parse_url($uri, PHP_URL_PATH);
        if (preg_match('#^/(?:checkin|w)/([^/]+)$#', $path, $matches) && ! array_key_exists('web_versione', $data)) {
            $r = app(WebCheckinLink::class)->resolve(rawurldecode($matches[1]));
            if ($r && \App\Models\Schedina::withoutGlobalScopes()->where('struttura_id', $r->struttura_id)->whereKey($r->schedina_id)->exists()) $data['web_versione'] = app(WebCheckinVersione::class)->of($r);
        }
        if (preg_match('#^/schedine/([0-9]+)$#', $path, $matches) && ! array_key_exists('web_versione_gestione', $data)) {
            $r = WebCheckinRichiesta::where('schedina_id', $matches[1])->first();
            if ($r && \App\Models\Schedina::withoutGlobalScopes()->where('struttura_id', $r->struttura_id)->whereKey($r->schedina_id)->exists()) $data['web_versione_gestione'] = app(WebCheckinVersione::class)->of($r);
        }
        if (preg_match('#^/web-checkin/([0-9]+)$#', $path, $matches) && ! array_key_exists('web_versione_richiesta', $data)) {
            $r = WebCheckinRichiesta::find($matches[1]);
            if ($r && \App\Models\Schedina::withoutGlobalScopes()->where('struttura_id', $r->struttura_id)->whereKey($r->schedina_id)->exists()) $data['web_versione_richiesta'] = app(WebCheckinVersione::class)->of($r);
        }

        return $data;
    }

    protected function postWeb($uri, array $data = [], array $headers = [])
    {
        return $this->post($uri, $this->webInput($uri, $data), $headers);
    }

    protected function postJsonWeb($uri, array $data = [], array $headers = [], $options = 0)
    {
        return $this->postJson($uri, $this->webInput($uri, $data), $headers, $options);
    }

    protected function putWeb($uri, array $data = [], array $headers = [])
    {
        return $this->put($uri, $this->webInput($uri, $data), $headers);
    }

    protected function putJsonWeb($uri, array $data = [], array $headers = [], $options = 0)
    {
        return $this->putJson($uri, $this->webInput($uri, $data), $headers, $options);
    }
}
