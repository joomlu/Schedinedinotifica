<?php

namespace Tests\Support;

trait QuesturaPreviewRequests
{
    /** Riproduce il controllo dell'anteprima corrente, lasciando al server Test/Send e le guardie. */
    protected function questuraPost(string $uri, array $data = [])
    {
        if (! array_key_exists('preview_hash', $data)) {
            if (preg_match('~/download/schedina/(\d+)~', $uri, $match)) {
                $schedina = \App\Models\Schedina::find($match[1]);
                if ($schedina) {
                    $analisi = app(\App\Services\QuesturaTxtExportService::class)->analizzaSchedine(collect([$schedina]));
                    if ($analisi->first()['valida']) {
                        $data['preview_hash'] = hash('sha256', implode("\r\n", $analisi->first()['righe']));
                    }
                }
            } else {
                $page = $this->get('/questura?'.http_build_query(array_intersect_key($data, array_flip(['dal', 'al']))));
                if ($page->baseResponse instanceof \Illuminate\Http\Response && $page->baseResponse->original instanceof \Illuminate\View\View) {
                    $data['preview_hash'] = $page->viewData('preview_hash');
                }
            }
        }

        return $this->post($uri, $data);
    }
}
