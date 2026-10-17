<?php

namespace App\Services;

use App\Models\TipoVia;

/** Conserva i casi ambigui; usa solo il catalogo attivo della struttura. */
class CustomerAddressNormalization
{
    public function normalize(array $input, int $sid): array
    {
        $data = [];
        foreach (['typeaway', 'address', 'number'] as $field) {
            $data[$field] = preg_replace('/\s+/u', ' ', trim((string) ($input[$field] ?? ''))) ?: null;
        }
        $errors = [];
        $types = TipoVia::withoutGlobalScopes()->where('struttura_id', $sid)->where('attivo', true)->get();
        $matches = [];
        foreach ($types as $type) {
            foreach (array_unique(array_filter([$type->nome, $type->abbr])) as $label) {
                if (preg_match('/^'.preg_quote($label, '/').'\.?\s+(.+)$/iu', $data['address'] ?? '', $m)) {
                    if (preg_match('/^\d+[\pL]?(?:\s*[\/\-]\s*[\pL\d]+)?$/u', trim($m[1]))) continue;
                    $matches[] = ['type' => $type, 'street' => $m[1], 'length' => mb_strlen($label)];
                }
            }
        }
        usort($matches, fn ($a, $b) => $b['length'] <=> $a['length']);
        $best = $matches[0] ?? null;
        if ($best && collect($matches)->where('length', $best['length'])->pluck('type.id')->unique()->count() > 1) {
            $errors['address'] = 'Tipo via ambiguo: verifica indirizzo e civico.';
            return compact('data', 'errors');
        }
        if ($best) {
            $allowed = [(string) $best['type']->id, $best['type']->nome, $best['type']->abbr];
            if ($data['typeaway'] && !collect($allowed)->contains(fn ($v) => mb_strtolower($v) === mb_strtolower($data['typeaway']))) {
                $errors['typeaway'] = 'Il tipo via non coincide con il prefisso dell’indirizzo.';
                return compact('data', 'errors');
            }
            $data['typeaway'] = $best['type']->nome;
            $data['address'] = $best['street'];
        }
        // Non separare indirizzi senza un tipo riconosciuto né nomi numerici di strada.
        $known = $types->contains(fn ($t) => in_array(mb_strtolower((string) $data['typeaway']), array_map(fn ($v) => mb_strtolower((string) $v), [$t->id, $t->nome, $t->abbr]), true));
        if ($known && preg_match('/^(.+?[\pL].*?)\s*,?\s+(\d+[\pL]?(?:\s*[\/\-]\s*[\pL\d]+)?)$/u', $data['address'] ?? '', $m)) {
            $number = preg_replace('/\s+/u', '', $m[2]);
            if ($data['number'] && mb_strtolower(str_replace(' ', '', $data['number'])) !== mb_strtolower($number)) {
                $errors['number'] = 'Il civico separato non coincide con quello dell’indirizzo.';
            } else {
                $data['address'] = rtrim(trim($m[1]), ',');
                $data['number'] = $number;
            }
        }
        return compact('data', 'errors');
    }
}
