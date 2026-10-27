<?php

namespace App\Services;

use App\Models\Struttura;
use Illuminate\Validation\ValidationException;

/** Il profilo è quello abilitato sull'utenza, mai dedotto dalla tipologia ricettiva. */
class QuesturaProfilo
{
    public function context(Struttura $struttura): array
    {
        $profilo = $struttura->questura_profilo ?? 'standard';
        if (! in_array($profilo, ['standard', 'appartamenti'], true)) {
            throw ValidationException::withMessages(['questura_profilo' => 'Profilo Alloggiati Web non valido.']);
        }
        if ($profilo === 'standard') {
            return ['profilo' => 'standard'];
        }
        $id = $struttura->questura_appartamento_id;
        // xsd:int, indice dell'elenco attivo. Il manuale contiene anche IDAPP 0.
        if ($id === null || ! preg_match('/\A[0-9]+\z/', (string) $id) || (float) $id > 2147483647) {
            throw ValidationException::withMessages(['questura_appartamento_id' => 'Indicare l’ID dell’appartamento attivo nell’elenco Alloggiati Web (intero da 0 a 2147483647).']);
        }

        return ['profilo' => 'appartamenti', 'id_appartamento' => (int) $id,
            'utenza_sha256' => hash('sha256', (string) $struttura->questura_username)];
    }
}
