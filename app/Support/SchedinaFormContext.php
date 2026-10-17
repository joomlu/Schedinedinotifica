<?php
namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class SchedinaFormContext
{
    public static function validate(Request $request, int $strutturaId): void
    {
        // I chiamanti legacy senza il campo restano compatibili; i moduli web sono vincolati all'apertura.
        $request->validate(['schedina_struttura_id'=>['nullable','integer','min:1']]);
        if ($request->filled('schedina_struttura_id') && (int) $request->input('schedina_struttura_id') !== $strutturaId) {
            throw ValidationException::withMessages(['schedina_struttura_id'=>'La struttura è cambiata da quando hai aperto il modulo. Nessun dato è stato salvato: riapri il modulo nella struttura desiderata.']);
        }
    }
}
