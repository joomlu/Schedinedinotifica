<?php

namespace App\Services;

use App\Models\{Customers, GeoNazione, GeoRegione, GeoProvincia, GeoComune, TipoVia, TipoDocumento, RilasciatoDa};
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Verifica i riferimenti originali prima della conversione nelle colonne testuali legacy. */
class CustomerWizardValidation
{
    public function initial(array $value, bool $preserveEmpty = false): array
    {
        $find = function (string $model, $raw, array $columns, ?string $parent = null, $parentId = null) {
            if ($raw === null || $raw === '') return null;
            $q = $model::query();
            if ($parent && $parentId) $q->where($parent, $parentId);
            $q->where(function ($query) use ($raw, $columns) {
                if (ctype_digit((string) $raw)) $query->where('id', $raw);
                else foreach ($columns as $column) $query->orWhere($column, $raw);
            });
            $records = $q->limit(2)->get();
            return $records->count() === 1 ? $records->first() : null;
        };
        $nation = $find(GeoNazione::class, $value['nazione'] ?? null, ['nome', 'codice_iso2']);
        $value['nazione_id'] = $nation?->id;
        if ($nation) {
            $value['nazione'] = $nation->nome;
            $value['manual'] = ! (bool) $nation->is_italia;
        } elseif ($preserveEmpty && empty($value['nazione'])) {
            // Il componente non deve precompilare Italia su un dato legacy mancante.
            $value['manual'] = true;
        }
        if (! $nation?->is_italia) return $value;
        $region = $find(GeoRegione::class, $value['regione_text'] ?? null, ['nome'], 'geo_nazione_id', $nation->id);
        $province = $find(GeoProvincia::class, $value['provincia_text'] ?? null, ['sigla', 'nome'], 'geo_regione_id', $region?->id);
        $city = $find(GeoComune::class, $value['comune_text'] ?? null, ['nome'], 'geo_provincia_id', $province?->id);
        $value['regione_id'] = $region?->id;
        $value['provincia_id'] = $province?->id;
        $value['comune_id'] = $city?->id;
        return $value;
    }

    public function validate(Request $request, ?Customers $old = null, ?int $strutturaId = null): void
    {
        $errors = [];
        $strictTransfer = $request->input('save_mode', $request->input('save_mode_intent')) === 'to_schedina';
        $lookup = function (string $field, string $model, array $columns, bool $active = false) use ($request, $old, $strictTransfer, $strutturaId, &$errors) {
            $value = $request->input($field);
            if ($value === null || $value === '') return null;
            $query = $model::query();
            if ($strutturaId !== null && in_array($model, [TipoVia::class, RilasciatoDa::class], true)) {
                $query = $model::withoutGlobalScopes()->where('struttura_id', $strutturaId);
            }
            $query->where(function ($q) use ($value, $columns) {
                if (ctype_digit((string) $value)) $q->where('id', $value);
                else foreach ($columns as $column) $q->orWhere($column, $value);
            });
            $record = $query->first();
            if ($record && (! $active || $record->attivo)) return $record;
            if ($old && (string) $old->getRawOriginal($field) === (string) $value && ($record || ! $strictTransfer)) return $record;
            $errors[$field] = 'Seleziona un valore valido del catalogo corrente.';
            return null;
        };
        foreach ([['country', 'region', 'province', 'city', 'cap'], ['country_reg', 'region_reg', 'prov_reg', 'city_reg', 'cap_reg'], ['country_az', 'region_az', 'province_az', 'city_az', 'cap_az']] as [$nationField, $regionField, $provinceField, $cityField, $capField]) {
            $nation = $lookup($nationField, GeoNazione::class, ['nome', 'codice_iso2']);
            $italia = (bool) $nation?->is_italia;
            if (! $italia) {
                // All'estero regione, provincia e città sono testi liberi previsti dal GEO ufficiale.
                foreach ([$regionField => GeoRegione::class, $provinceField => GeoProvincia::class, $cityField => GeoComune::class] as $field => $model) {
                    if (ctype_digit((string) $request->input($field, '')) && $model::find($request->input($field))) {
                        $errors[$field] = 'Il riferimento italiano non è coerente con la nazione selezionata.';
                    }
                }
                continue;
            }
            $region = $lookup($regionField, GeoRegione::class, ['nome']);
            $province = $lookup($provinceField, GeoProvincia::class, ['sigla', 'nome']);
            $city = $lookup($cityField, GeoComune::class, ['nome']);
            if ($region && $province && (int) $province->geo_regione_id !== (int) $region->id) $errors[$provinceField] = 'La provincia non appartiene alla regione selezionata.';
            if ($city && $province && (int) $city->geo_provincia_id !== (int) $province->id) $errors[$cityField] = 'Il comune non appartiene alla provincia selezionata.';
            if ($city && $region && (int) $city->provincia?->geo_regione_id !== (int) $region->id) $errors[$cityField] = 'Il comune non appartiene alla regione selezionata.';
            $cap = $request->input($capField);
            if ($cap && ! preg_match('/^[0-9]{5}$/', $cap)) $errors[$capField] = 'Il CAP italiano deve contenere cinque cifre.';
            elseif ($cap && $city && $city->caps()->exists() && ! $city->caps()->where('cap', $cap)->exists()) $errors[$capField] = 'Il CAP non appartiene al comune selezionato.';
        }
        $lookup('country_doc_reg', GeoNazione::class, ['nome', 'codice_iso2']);
        $documentCountry = $request->input('country_doc_reg');
        $italianDocument = GeoNazione::query()->where('is_italia', true)->where(function ($q) use ($documentCountry) {
            $q->where('nome', $documentCountry)->orWhere('codice_iso2', $documentCountry);
            if (ctype_digit((string) $documentCountry)) $q->orWhere('id', $documentCountry);
        })->exists();
        if ($italianDocument) $lookup('city_doc_reg', GeoComune::class, ['nome']);
        $lookup('typeaway', TipoVia::class, ['nome', 'abbr'], true);
        $lookup('typeaway_az', TipoVia::class, ['nome', 'abbr'], true);
        $lookup('rilasciato_reg', RilasciatoDa::class, ['name'], true);
        $lookup('type_doc_reg', TipoDocumento::class, ['descrizione', 'codice']);
        if ($request->filled('ciudadania_reg') && ! GeoNazione::query()->where('cittadinanza', $request->input('ciudadania_reg'))->exists()
            && ($strictTransfer || ! $old || $old->ciudadania_reg !== $request->input('ciudadania_reg'))) $errors['ciudadania_reg'] = 'Seleziona una cittadinanza del catalogo ufficiale.';
        if ($errors) throw ValidationException::withMessages($errors);
    }
}
