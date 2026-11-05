<?php

namespace App\Services\TassaConfig;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/** Riconoscimento prudente: un dato assente o più valori non diventa mai una regola. */
class AnalisiDocumentoTassa
{
    public function testo(string $bytes, string $tipo): array
    {
        if ($tipo !== 'pdf') {
            if ($tipo === 'html') {
                $dom = new \DOMDocument;
                @$dom->loadHTML('<?xml encoding="UTF-8">'.str_replace(['</p>', '</tr>', '</div>'], "\n", $bytes));
                foreach (['script', 'style', 'nav', 'header', 'footer'] as $tag) {
                    foreach (iterator_to_array($dom->getElementsByTagName($tag)) as $node) {
                        $node->parentNode->removeChild($node);
                    }
                }
                $bytes = $dom->textContent;
            }

            return ['testo' => $bytes, 'limite' => null];
        }
        $bin = (new ExecutableFinder)->find('pdftotext');
        if (! $bin) {
            return ['testo' => '', 'limite' => 'Estrazione PDF non disponibile su questo server. Conservato l’originale: incollare il testo indicando pagina o articolo. OCR e servizi esterni non sono disponibili.'];
        }
        $file = tempnam(sys_get_temp_dir(), 'tassa-pdf-');
        try {
            file_put_contents($file, $bytes);
            $process = new Process([$bin, '-layout', '-enc', 'UTF-8', $file, '-']);
            $process->setTimeout(20);
            $output = '';
            $process->run(function ($tipo, $buffer) use (&$output) {
                if ($tipo === Process::OUT) {
                    $output .= $buffer;
                }
                if (strlen($output) > 1024 * 1024) {
                    throw new \RuntimeException('Estrazione troppo estesa');
                }
            });
            if (! $process->isSuccessful() || mb_strlen(trim($process->getOutput())) < 50) {
                return ['testo' => '', 'limite' => 'Il PDF non contiene testo leggibile o è protetto. Serve trascrizione con pagine/articoli; OCR non disponibile.'];
            }
            $pages = explode("\f", $process->getOutput());

            return ['testo' => collect($pages)->map(fn ($p, $i) => 'Pagina '.($i + 1)."\n".$p)->implode("\n"), 'limite' => null];
        } catch (\Throwable $e) {
            return ['testo' => '', 'limite' => 'Estrazione PDF non riuscita entro i limiti di tempo o dimensione. Originale conservato; serve una trascrizione con pagine/articoli.'];
        } finally {
            unlink($file);
        }
    }

    public function estrai(string $testo, int $anno): array
    {
        $valori = [];
        $riferimenti = [];
        $problemi = [];
        $annoIncompatibile = false;
        $patterns = [
            'valida_dal' => '/valid[ia](?:tà|ta|t[aà])?\s+dal\s+(\d{2}\/\d{2}\/\d{4})/iu',
            'valida_al' => '/valid[ia](?:tà|ta|t[aà])?\s+dal\s+\d{2}\/\d{2}\/\d{4}\s+al\s+(\d{2}\/\d{2}\/\d{4})/iu',
            'inizio' => '/periodo(?: di applicazione)?(?: dell[’\']imposta)?\s*:?\s*dal\s+(\d{2}\/\d{2}\/\d{4})/iu',
            'fine' => '/periodo(?: di applicazione)?(?: dell[’\']imposta)?\s*:?\s*dal\s+\d{2}\/\d{2}\/\d{4}\s+al\s+(\d{2}\/\d{2}\/\d{4})/iu',
            'giorni_massimo' => '/massimo\s+(\d+)\s+pernottamenti\s+consecutivi/iu',
            'max_age_children' => '/esenzione\s+fino\s+a\s+(\d+)\s+anni\s+inclusi/iu',
            'min_age_adult' => '/et[aà]\s+minima\s+imponibile\s*:?\s*(\d+)/iu',
        ];
        foreach (range(1, 5) as $classe) {
            $patterns['tariffa_'.$classe] = '/(?:albergo|alberghi)\s+'.$classe.'\s+stell[ae]\s*[:–-]?\s*(?:€|euro)\s*(\d+(?:[,.]\d{1,2})?)(?!\d|[,.]\d|[eE][+-]?\d)/iu';
        }
        $nomi = ['valida_dal' => 'Inizio validità', 'valida_al' => 'Fine validità', 'inizio' => 'Inizio periodo dell’imposta', 'fine' => 'Fine periodo dell’imposta', 'giorni_massimo' => 'Limite notti consecutive', 'max_age_children' => 'Età massima esenzione', 'min_age_adult' => 'Età minima imponibile'];
        foreach ($patterns as $campo => $pattern) {
            preg_match_all($pattern, $testo, $matches, PREG_OFFSET_CAPTURE);
            $unici = array_unique(array_column($matches[1] ?? [], 0));
            if (count($unici) !== 1) {
                $problemi[] = ($nomi[$campo] ?? 'Tariffa alberghi '.substr($campo, -1).' stelle').': '.(count($unici) ? 'valori discordanti nel documento.' : 'dato non riconosciuto in modo univoco; indicare il passaggio ufficiale.');

                continue;
            }
            $v = reset($unici);
            if (in_array($campo, ['valida_dal', 'valida_al', 'inizio', 'fine'], true)) {
                $d = \DateTimeImmutable::createFromFormat('!d/m/Y', $v);
                if ($d && $d->format('d/m/Y') === $v && (int) $d->format('Y') !== $anno && in_array($campo, ['valida_dal', 'valida_al'], true)) {
                    $annoIncompatibile = true;
                }
                if (! $d || $d->format('d/m/Y') !== $v || (int) $d->format('Y') !== $anno) {
                    $problemi[] = ($nomi[$campo]).': data non valida o non riferita all’anno selezionato.';

                    continue;
                }
                $v = $d->format('Y-m-d');
            } else {
                if (str_starts_with($campo, 'tariffa_')) {
                    $raw = str_replace(',', '.', $v);
                    $parts = explode('.', $raw);
                    $canonico = (ltrim($parts[0], '0') ?: '0').'.'.str_pad($parts[1] ?? '', 2, '0');
                    if (! is_finite((float) $raw) || number_format((float) $raw, 2, '.', '') !== $canonico) {
                        $problemi[] = 'Tariffa non rappresentabile con precisione dal calcolo attuale.';

                        continue;
                    }
                    $v = $canonico;
                } else {
                    $v = (int) $v;
                }
            }
            $valori[$campo] = $v;
            $offset = $matches[1][0][1];
            $prima = substr($testo, 0, $offset);
            preg_match_all('/(?:Pagina\s+\d+|Art(?:icolo|\.)\s*\d+)/iu', $prima, $refs);
            $riferimenti[$campo] = ['riferimento' => end($refs[0]) ?: 'Testo, carattere '.$offset, 'passaggio' => $matches[0][0][0]];
        }
        if (! preg_match('/imposta\s+di\s+soggiorno/iu', $testo) || ! preg_match('/per(?:\s+ciascuna)?\s+persona/iu', $testo) || ! preg_match('/per\s+(?:notte|pernottamento)/iu', $testo)) {
            $problemi[] = 'Unità della tariffa: identificare esplicitamente l’imposta di soggiorno per persona e per notte. Nessuna conversione da quote per camera o altri tributi.';
        }
        if (isset($valori['giorni_massimo']) && $valori['giorni_massimo'] !== 6) {
            $problemi[] = 'Limite notti diverso da 6: compatibilità con il tracciato StayTour non documentata; nessuna applicazione automatica.';
        }
        if (! preg_match('/esenzioni\s+(?:restano|rimangono)\s+invariate/iu', $testo)) {
            $problemi[] = 'Esenzioni: manca una conferma esplicita che restino invariate rispetto al profilo vigente. Non vengono modificate dal sistema.';
        }
        preg_match_all('/(?:efficace|in vigore)\s+dal\s+(\d{2}\/\d{2}\/\d{4})/iu', $testo, $efficacia);
        $dateEfficacia = array_unique($efficacia[1]);
        $dataAttesa = isset($valori['valida_dal']) ? \Carbon\Carbon::parse($valori['valida_dal'])->format('d/m/Y') : null;
        if (count($dateEfficacia) !== 1 || reset($dateEfficacia) !== $dataAttesa) {
            $problemi[] = 'Efficacia: manca una decorrenza espressa per l’anno selezionato; approvazione e pubblicazione non dimostrano da sole la vigenza.';
        }
        if (! preg_match('/Comune\s+di\s+Bellaria[-\s]+Igea\s+Marina/iu', $testo)) {
            $problemi[] = 'Comune: manca un riferimento inequivoco a Bellaria-Igea Marina, unico profilo certificato supportato dal motore attuale.';
        }
        preg_match_all('/(?:per\s+l[’\x27]anno|annualit[aà]|anno\s+fiscale)\s*(\d{4})/iu', $testo, $annualita);
        $anni = array_unique($annualita[1]);
        if (count($anni) === 1 && (int) reset($anni) !== $anno) {
            $annoIncompatibile = true;
        }
        if ($annoIncompatibile) {
            $valori = [];
            $riferimenti = [];
            $problemi[] = 'Il documento è riferito a un altro anno. Non vengono riproposti i suoi importi per completare questa annualità.';
        }

        return compact('valori', 'riferimenti', 'problemi');
    }
}
