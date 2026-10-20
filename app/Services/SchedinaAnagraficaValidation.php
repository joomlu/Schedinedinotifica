<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Adatta i nomi legacy della schedina al controllo GEO/cataloghi già usato dai clienti. */
class SchedinaAnagraficaValidation
{
    public function validate(Request $request, int $strutturaId, bool $complete = false): void
    {
        $map = [
            'oa_country'=>'country_reg', 'oa_region'=>'region_reg', 'oa_prov'=>'prov_reg', 'oa_city'=>'city_reg', 'oa_cap'=>'cap_reg',
            'or_country'=>'country', 'or_region'=>'region', 'or_prov'=>'province', 'or_city'=>'city', 'or_cap'=>'cap',
            'oa_city_nac'=>'ciudadania_reg', 'or_typeaway'=>'typeaway', 'or_doctype'=>'type_doc_reg',
            'or_published'=>'rilasciato_reg', 'or_published_country'=>'country_doc_reg', 'or_published_city'=>'city_doc_reg',
        ];
        $values = ['save_mode'=>'to_schedina'];
        foreach ($map as $source=>$target) $values[$target]=$request->input($source);
        try {
            app(CustomerWizardValidation::class)->validate(new Request($values), null, $strutturaId);
        } catch (ValidationException $e) {
            $reverse=array_flip($map); $errors=[];
            foreach ($e->errors() as $field=>$messages) $errors[$reverse[$field] ?? $field]=$messages;
            throw ValidationException::withMessages($errors);
        }
        foreach (\App\Support\Componenti\DatiComponenteNormalizzati::normalizzaRighe((array) $request->input('componenti', [])) as $index => $row) {
            $country = $row['country'] ?? null;
            $italian = \App\Models\GeoNazione::query()->where('is_italia', true)->where(function ($q) use ($country) {
                $q->where('nome', $country)->orWhere('codice_iso2', $country);
                if (ctype_digit((string) $country)) $q->orWhere('id', $country);
            })->exists();
            $rules = ['country' => [$complete ? 'required' : 'nullable', 'string']];
            foreach (['regione', 'province', 'city'] as $field) $rules[$field] = [$complete && $italian ? 'required' : 'nullable', 'string'];
            $required = \Illuminate\Support\Facades\Validator::make($row, $rules, ['required'=>'Seleziona :attribute del componente.', 'string'=>':attribute del componente deve essere un testo.'], ['country'=>'la nazione di residenza', 'regione'=>'la regione di residenza', 'province'=>'la provincia di residenza', 'city'=>'il comune di residenza']);
            $errors = [];
            foreach ($required->errors()->messages() as $field => $messages) $errors["componenti.$index.$field"] = $messages;
            $componentMap = ['country_nac'=>'country_reg', 'regione_nac'=>'region_reg', 'province_nac'=>'prov_reg', 'comune_nac'=>'city_reg', 'cap_nac'=>'cap_reg', 'city_nac'=>'ciudadania_reg', 'country'=>'country', 'regione'=>'region', 'province'=>'province', 'city'=>'city', 'cap'=>'cap', 'typeaway'=>'typeaway'];
            $values = ['save_mode'=>'to_schedina'];
            foreach ($componentMap as $source=>$target) $values[$target]=$row[$source] ?? null;
            try {
                app(CustomerWizardValidation::class)->validate(new Request($values), null, $strutturaId);
            } catch (ValidationException $e) {
                $reverse = array_flip($componentMap);
                foreach ($e->errors() as $field=>$messages) $errors["componenti.$index.".($reverse[$field] ?? $field)]=$messages;
            }
            if ($errors) throw ValidationException::withMessages($errors);
        }
    }
}
