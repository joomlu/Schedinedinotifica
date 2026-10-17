<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Adatta i nomi legacy della schedina al controllo GEO/cataloghi già usato dai clienti. */
class SchedinaAnagraficaValidation
{
    public function validate(Request $request, int $strutturaId): void
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
    }
}
