<?php

namespace App\Services\TassaConfig;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class FonteUfficialeTassa
{
    protected function indirizzi(string $host): array
    {
        return array_unique(array_merge(gethostbynamel($host) ?: [], array_column(dns_get_record($host, DNS_AAAA) ?: [], 'ipv6')));
    }

    public function scarica(string $url, string $comune, array $collegati = []): array
    {
        $p = parse_url($url);
        $hosts = config('tassa_fonti.'.$comune.'.hosts', []);
        if (($p['scheme'] ?? '') !== 'https' || isset($p['user']) || isset($p['pass']) || isset($p['fragment']) || (isset($p['port']) && $p['port'] !== 443)
            || (! in_array($p['host'] ?? '', $hosts, true) && ! in_array($url, $collegati, true))) {
            throw ValidationException::withMessages(['fonte' => 'Il collegamento deve appartenere al sito comunale verificato o essere un documento collegato direttamente dalla sua pagina ufficiale.']);
        }
        $ips = $this->indirizzi($p['host']);
        if (! $ips || collect($ips)->contains(fn ($ip) => ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))) {
            throw ValidationException::withMessages(['fonte' => 'La fonte non è raggiungibile su un indirizzo pubblico verificabile. Nessuna conclusione su nuove disposizioni.']);
        }
        try {
            $response = Http::connectTimeout(5)->timeout(20)->withOptions([
                'allow_redirects' => false, 'verify' => true, 'stream' => true,
                'curl' => [CURLOPT_RESOLVE => [$p['host'].':443:'.(str_contains($ips[0], ':') ? '['.$ips[0].']' : $ips[0])]],
            ])->get($url);
            if (! $response->successful()) {
                throw new \RuntimeException('Risposta non utilizzabile');
            }
            $body = $response->toPsrResponse()->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }
            $bytes = '';
            while (! $body->eof()) {
                $bytes .= $body->read(65536);
                if (strlen($bytes) > 8 * 1024 * 1024) {
                    throw new \RuntimeException('Documento troppo grande');
                }
            }
            if ($bytes === '') {
                throw new \RuntimeException('Documento vuoto');
            }
            if (! str_starts_with($bytes, '%PDF-') && strlen($bytes) > 1024 * 1024) {
                throw new \RuntimeException('Pagina troppo estesa');
            }

            return ['url' => $url, 'bytes' => $bytes, 'tipo' => str_starts_with($bytes, '%PDF-') ? 'pdf' : (str_contains($bytes, '<') ? 'html' : 'testo'), 'verificata_il' => now()->toIso8601String()];
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['fonte' => 'Non è stato possibile leggere il documento ufficiale (connessione, risposta o dimensione). La verifica resta incompleta; non significa che manchino nuove disposizioni.']);
        }
    }

    public function collegamenti(string $html, string $base): array
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $urls = [];
        foreach ($dom->getElementsByTagName('a') as $a) {
            $href = html_entity_decode($a->getAttribute('href'));
            if (! preg_match('/impost|soggiorn|ids|regolament|tariff|deliber/iu', $a->textContent.' '.$href)) {
                continue;
            }
            if (str_starts_with($href, '/')) {
                $href = 'https://'.parse_url($base, PHP_URL_HOST).$href;
            }
            if (str_starts_with($href, 'https://')) {
                $urls[] = $href;
            }
        }

        return array_values(array_unique($urls));
    }
}
