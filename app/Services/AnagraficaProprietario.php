<?php

namespace App\Services;

use Illuminate\Support\Arr;

class AnagraficaProprietario
{
    public static function rules(bool $create): array
    {
        $rules = ['nome' => [$create ? 'required' : 'sometimes', 'filled', 'string', 'max:255']];
        foreach (['email' => 255, 'pec' => 180] as $key => $max) $rules[$key] = ['sometimes', 'nullable', 'email', 'max:'.$max];
        foreach (['telefono' => 50, 'cellulare' => 50, 'ragione_sociale' => 180, 'codice_fiscale' => 32, 'partita_iva' => 32,
            'codice_destinatario' => 32, 'codice_unico' => 7, 'indirizzo' => 180, 'numero_civico' => 20, 'cap' => 16,
            'citta' => 120, 'provincia' => 8, 'regione' => 120, 'nazione' => 120] as $key => $max) {
            $rules[$key] = ['sometimes', 'nullable', 'string', 'max:'.$max];
        }
        $rules['latitudine'] = ['sometimes', 'nullable', 'numeric', 'between:-90,90'];
        $rules['longitudine'] = ['sometimes', 'nullable', 'numeric', 'between:-180,180'];
        $rules['note'] = ['sometimes', 'nullable', 'string'];
        $rules['note_amministrative'] = ['sometimes', 'nullable', 'string'];
        return $rules;
    }

    public static function payload(array $data): array
    {
        return Arr::only($data, array_keys(self::rules(false)));
    }
}
