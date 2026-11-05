<?php
namespace App\Services;

use App\Models\Struttura;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/** Le date di apertura esistenti indicano giorni aperti inclusivi, come la Tavola A. */
class SchedinaSoggiornoValidation
{
    public function validate(Struttura $struttura, string $arrivo, string $partenza): void
    {
        $from = CarbonImmutable::parse($arrivo)->startOfDay();
        $to = CarbonImmutable::parse($partenza)->startOfDay();
        if ($to->lte($from)) throw ValidationException::withMessages(['departure'=>'La partenza deve essere successiva all’arrivo.']);
        if ($struttura->tipo_apertura === 'Annuale') return;
        if (!$struttura->data_apertura || !$struttura->data_chiusura || $struttura->data_chiusura->lt($struttura->data_apertura)) {
            throw ValidationException::withMessages(['arrive'=>'Completa il periodo di apertura nelle impostazioni della struttura prima di salvare definitivamente. La bozza resta disponibile.']);
        }
        // Il check-out non aggiunge una notte: confrontare l'ultima notte, non la data di partenza.
        if ($from->lt($struttura->data_apertura)) throw ValidationException::withMessages(['arrive'=>'L’arrivo precede il periodo di apertura configurato.']);
        if ($from->gt($struttura->data_chiusura) || $to->subDay()->gt($struttura->data_chiusura)) {
            throw ValidationException::withMessages(['departure'=>'Il soggiorno comprende notti fuori dal periodo di apertura configurato.']);
        }
    }
}
