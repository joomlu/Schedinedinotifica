<?php

namespace App\Services;

final class IstatResponseParser
{
    public function parse(int $http, string $body, string $payload): array
    {
        $unknown = EsitoTrasmissioneIstat::crea('uncertain', 'send', $http);
        if ($http < 200 || $http >= 300 || strlen($body) > 2097152) {
            return $unknown;
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $validator = new IstatXmlValidator;
            $doc = $validator->document($body);
            $xp = new \DOMXPath($doc);
            $xp->registerNamespace('s', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xp->registerNamespace('r', IstatXmlValidator::WS_NAMESPACE);
            $nodes = $xp->query('/s:Envelope/s:Body/*');
            if ($nodes->length !== 1 || $nodes->item(0)->localName !== 'inviaMovimentazioneResponse'
                || $nodes->item(0)->namespaceURI !== IstatXmlValidator::WS_NAMESPACE) {
                return $unknown;
            }
            $operation = new \DOMDocument;
            $operation->appendChild($operation->importNode($nodes->item(0), true));
            if (! $operation->schemaValidate(base_path('reference/istat/ross1000-er/checkin.xsd'))) {
                return $unknown;
            }
            $original = new \DOMXPath($validator->document($payload));
            $expected = [];
            $actual = [];
            $positive = 0;
            $negative = 0;
            foreach (['arrivi/arrivo', 'partenze/partenza', 'prenotazioni/prenotazione', 'retifiche/eliminazione'] as $category) {
                foreach ($original->query('/movimenti/movimento/'.$category) as $record) {
                    $key = $category.':'.$original->evaluate('string(idswh)', $record);
                    $expected[$key] = ($expected[$key] ?? 0) + 1;
                }
                foreach ($xp->query('/s:Envelope/s:Body/r:inviaMovimentazioneResponse/return/risultatiGiorno/'.$category) as $record) {
                    $key = $category.':'.$xp->evaluate('string(idswh)', $record);
                    $actual[$key] = ($actual[$key] ?? 0) + 1;
                    $success = $xp->evaluate('string(successo)', $record);
                    if (in_array($success, ['true', '1'], true)) {
                        if (trim($xp->evaluate('string(errore)', $record)) !== '') {
                            return $unknown;
                        }
                        $positive++;
                    } elseif (in_array($success, ['false', '0'], true)) {
                        $negative++;
                    } else {
                        return $unknown;
                    }
                }
            }
            ksort($actual);
            ksort($expected);
            // Nessun esito esplicito del calendario: una risposta vuota non prova nulla.
            if (! $expected || $expected !== $actual || max($actual) > 1) {
                return $unknown;
            }

            return EsitoTrasmissioneIstat::crea($negative ? ($positive ? 'partial' : 'rejected') : 'processed', 'send', $http);
        } catch (\Throwable) {
            return $unknown;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
