<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

final class IstatXmlValidator
{
    public const WS_NAMESPACE = 'http://checkin.ws.service.turismo5.gies.it/';

    public function document(string $xml): \DOMDocument
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $doc = new \DOMDocument();
            if (str_contains($xml, '<!DOCTYPE') || !$doc->loadXML($xml, LIBXML_NONET) || $doc->doctype) {
                $this->fail('XML non valido o contenente una dichiarazione DTD vietata.');
            }
            return $doc;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    public function validate(string $xml): void
    {
        $doc = $this->document($xml);
        $previous = libxml_use_internal_errors(true);
        try {
            if (!$doc->schemaValidate(base_path('reference/istat/ross1000-er/file-binding.xsd'))) {
                $this->fail('XML incompatibile con lo schema XSD regionale. Verificare i dati origine indicati nell’anteprima.');
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['istat_export' => $message]);
    }
}
