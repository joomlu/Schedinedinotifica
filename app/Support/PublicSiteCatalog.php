<?php

namespace App\Support;

final class PublicSiteCatalog
{
    public function data(): array
    {
        return json_decode(file_get_contents(base_path('web/assets/catalogo-commerciale.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function plans(): array
    {
        return $this->data()['plans'];
    }

    public function render(string $html): string
    {
        $tokens = [];
        $flatten = function (array $values, string $prefix = '') use (&$flatten, &$tokens): void {
            foreach ($values as $key => $value) {
                $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
                if (is_array($value)) {
                    $flatten($value, $path);
                } else {
                    $tokens['{{catalog.'.$path.'}}'] = htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
            }
        };
        $data = $this->data();
        $flatten($data);
        // Alias brevi per i piani nei template HTML pubblici esistenti.
        foreach ($data['plans'] as $code => $plan) {
            $flatten($plan, $code);
        }
        $rendered = strtr($html, $tokens);
        if (preg_match('/\{\{catalog\.[^}]+\}\}/', $rendered)) {
            throw new \LogicException('Riferimento al catalogo commerciale non riconosciuto.');
        }

        return $rendered;
    }

    public function selection(?string $planCode, bool $maxFull): string
    {
        $data = $this->data();
        $lines = [];
        if ($planCode !== null && $planCode !== '') {
            $plan = $data['plans'][$planCode];
            $lines[] = 'Piano richiesto: '.$plan['name'].' ['.$planCode.']';
            $lines[] = 'Ambito: '.$plan['scope'];
            $lines[] = 'Canone: '.$plan['price'];
            $lines[] = $data['annual_note'];
            $lines[] = $data['extras_note'];
        }
        if ($maxFull) {
            $lines[] = 'Servizio aggiuntivo richiesto: '.$data['max_full']['name'];
            $lines[] = $data['max_full']['price'];
            $lines[] = $data['max_full']['commercial_note'];
        }

        return implode("\n", $lines);
    }
}
