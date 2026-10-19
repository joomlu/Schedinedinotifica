<?php

namespace App\Services;

use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use App\Support\Componenti\DatiComponenteNormalizzati;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Compilazione, classificazione alla data d'arrivo e presa in carico restano distinte. */
class WebCheckinCircuito
{
    // Ripartizione anagrafica 0–17/18+ già presente nel progetto, non esenzione fiscale o tipo alloggiato.
    public const ETA_ADULTO = 18;

    public function verificaPersone(WebCheckinRichiesta $r, Request $input): array
    {
        $births = [$input->input('oa_date_nac')];
        foreach (DatiComponenteNormalizzati::normalizzaRighe((array) $input->input('componenti', []), true) as $row) $births[] = $row['date_nac'] ?? null;
        $adulti = $bambini = 0;
        foreach ($births as $birth) {
            if (blank($birth)) throw ValidationException::withMessages(['cant_people' => 'Completa la data di nascita di ogni ospite.']);
            $date = Carbon::parse($birth)->startOfDay();
            if ($date->gt($r->arrivo)) throw ValidationException::withMessages(['cant_people' => 'La nascita di un ospite è successiva all’arrivo previsto.']);
            if ($date->copy()->addYears(self::ETA_ADULTO)->lte($r->arrivo)) $adulti++; else $bambini++;
        }
        if (count($births) !== (int) $r->quantita_persone || (int) $input->input('cant_people') !== (int) $r->quantita_persone) {
            throw ValidationException::withMessages(['cant_people' => 'Gli ospiti inseriti devono corrispondere al totale della richiesta.']);
        }
        if ($r->numero_adulti !== null && ($adulti !== $r->numero_adulti || $bambini !== $r->numero_bambini)) {
            throw ValidationException::withMessages(['cant_people' => 'Le date di nascita non corrispondono agli adulti e bambini previsti. Verifica gli ospiti o contatta la reception.']);
        }
        return ['numero_adulti' => $adulti, 'numero_bambini' => $bambini];
    }

    public function snapshot(Schedina $sheet): array
    {
        $head = array_filter($sheet->getAttributes(), fn ($key) => in_array($key, ['id', 'name', 'surname', 'sex', 'relationship', 'arrive', 'departure', 'cant_people', 'room', 'beds', 'customer_email', 'customer_phone', 'customer_cellphone', 'customer_privacy_consent', 'customer_privacy_consent_at'], true) || str_starts_with($key, 'oa_') || str_starts_with($key, 'or_'), ARRAY_FILTER_USE_KEY);
        $people = Componenti::withoutGlobalScopes()->where('struttura_id', $sheet->struttura_id)->where('schedina_id', $sheet->id)->orderBy('id')->get();
        return ['salvato_il' => now()->toIso8601String(), 'titolare' => $head, 'componenti' => $people->map(fn ($p) => $p->only(['id','customer_id','name','surname','sex','relationship','exent','date_nac','country_nac','city_nac','regione_nac','province_nac','comune_nac','cap_nac','country','regione','province','city','cap','typeaway','address','number']))->all()];
    }

    public function finalizza(WebCheckinRichiesta $r, Schedina $sheet): void
    {
        // Il chiamante detiene lock struttura/richiesta/schedina nella transazione canonica.
        abort_unless(\Illuminate\Support\Facades\DB::transactionLevel() > 0 && $sheet->circuito === 'schedina' && (int) $sheet->struttura_id === (int) $r->struttura_id, 409);
        $customer = app(CustomerImportService::class)->clienteDaSchedinaWeb($sheet);
        $sheet->forceFill(['customer_id' => $customer->id])->save();
        Componenti::withoutGlobalScopes()->where('struttura_id', $sheet->struttura_id)->where('schedina_id', $sheet->id)->whereNull('customer_id')->update(['customer_id' => $customer->id]);
        $r->forceFill(['conversione_snapshot' => $this->snapshot($sheet), 'stato' => 'convertito', 'convertito_at' => now()])->save();
    }
}
