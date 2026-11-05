<?php

namespace App\Services;

use App\Models\{Customers, GeoNazione, TipoCliente};
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

/** Regole uniche di salvataggio per wizard e importatore. */
class CustomerInputValidation
{
    public function normalizeTipoCliente($value): string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return '';
        }

        $map = [
            'ospite' => 'Ospite',
            'componente' => 'Componente',
            'richiesta' => 'Richiesta',
        ];

        $key = mb_strtolower($normalized);
        return $map[$key] ?? $normalized;
    }

    public function validate(Request $request, string $saveMode = 'final', ?int $strutturaId = null): void
    {
        $validator = Validator::make(
            $request->all(),
            $this->rulesByTipoCliente($request->input('type_cliente'), $saveMode, $request),
            $this->validationMessages(),
            $this->validationAttributes()
        );
        $validator->after(function ($validator) use ($request, $strutturaId) {
            $this->validateGruppiCascade($validator, $request, $strutturaId);
            $this->validateSchedinaTransferMode($validator, $request);
        });

        $validator->validate();
    }

    private function rulesByTipoCliente(?string $tipoCliente, string $saveMode, Request $request): array
    {
        $tipo = $this->normalizeTipoCliente($tipoCliente);
        $nameRule = ['required', 'string', 'max:191', 'regex:/^[\pL\s\'\-\.]+$/u'];
        $phoneRule = ['nullable', 'string', 'max:191', 'regex:/^[0-9\+\(\)\s\-\/]*$/', 'regex:/[0-9]/'];
        $emailRule = ['nullable', 'email', 'max:191'];
        $websiteRule = ['nullable', 'url', 'max:191'];

        $base = [
            'submission_id' => ['nullable', 'uuid'],
            'active_tab' => ['nullable', Rule::in(['steparrow-gen-info', 'steparrow-description-info', 'steparrow-azienda-info', 'steparrow-contact-info'])],
            'has_azienda' => ['nullable', 'boolean'],
            'save_mode' => ['nullable', Rule::in(['draft', 'final', 'to_schedina'])],
            'save_mode_intent' => ['nullable', Rule::in(['draft', 'final', 'to_schedina'])],
            'type_cliente' => ['required', Rule::in(TipoCliente::query()->diSistema()->where('attivo', true)->pluck('descrizione')->all())],
            'name' => $nameRule,
            'surname' => $nameRule,
            'group' => ['nullable', 'string', 'max:191'],
            'subgroup' => ['nullable', 'string', 'max:191'],
            'subgroup1' => ['nullable', 'string', 'max:191'],
            'email' => $emailRule,
            'phone' => $phoneRule,
            'fax' => $phoneRule,
            'cellphone' => $phoneRule,
            'sex' => ['nullable', Rule::in(['M', 'F'])],
            'privacy_consent' => ['nullable', 'boolean'],
            'marketing_consent' => ['nullable', 'boolean'],
            'communication_consent' => ['nullable', 'boolean'],
            'website' => $websiteRule,
            'num_doc_reg' => ['nullable', 'string', 'max:191', 'regex:/^[A-Za-z0-9\/\-\s]+$/'],
            'cf_az' => ['nullable', 'string', 'max:16', 'regex:/^[A-Za-z0-9]+$/'],
            'pi_az' => ['nullable', 'digits:11'],
            'sdi_az' => ['nullable', 'size:7', 'regex:/^[A-Z0-9]+$/'],
            'phone_az' => $phoneRule,
            'fax_az' => $phoneRule,
            'cellphone_az' => $phoneRule,
            'email_az' => $emailRule,
            'country_az' => ['nullable', 'string', 'max:191'],
            'city_az' => ['nullable', 'string', 'max:191'],
        ];

        foreach (['country', 'region', 'province', 'city', 'cap', 'typeaway', 'address', 'number', 'country_reg', 'region_reg', 'prov_reg', 'city_reg', 'cap_reg', 'ciudadania_reg', 'type_doc_reg', 'rilasciato_reg', 'country_doc_reg', 'city_doc_reg', 'azienda', 'region_az', 'province_az', 'cap_az', 'typeaway_az', 'address_az', 'number_az', 'observation', 'observation_reg', 'desc_az', 'nota'] as $field) {
            $base[$field] ??= ['nullable', 'string', 'max:191'];
        }
        foreach (['cap_reg', 'cap_az', 'number_az'] as $field) $base[$field] = ['nullable', 'string', 'max:20'];
        foreach (['desc_az', 'nota'] as $field) $base[$field] = ['nullable', 'string', function ($attribute, $value, $fail) {
            if (strlen($value) > 65535) $fail('Il testo supera la capacità del campo.');
        }];
        $base['nac_reg'] = ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'];
        $base['date_pub_reg'] = ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'];
        $base['expire_reg'] = array_filter(['nullable', 'date_format:Y-m-d', $request->filled('date_pub_reg') ? 'after_or_equal:date_pub_reg' : null]);

        if ($tipo === 'Richiesta') {
            return array_replace($base, [
                'email' => ['required_without_all:phone,cellphone', 'nullable', 'email', 'max:191'],
                'phone' => $phoneRule,
            ]);
        }

        if ($saveMode === 'draft') {
            return $base;
        }

        $required = ['sex', 'country_reg', 'ciudadania_reg', 'nac_reg'];
        $country = GeoNazione::query()->where('nome', $request->input('country_reg'))->first();
        if ($country?->is_italia) {
            $required[] = 'city_reg';
            $required[] = 'prov_reg';
        }
        if ($tipo === 'Ospite') {
            array_push($required, 'type_doc_reg', 'num_doc_reg', 'country_doc_reg');
            $documentCountry = GeoNazione::query()->where('nome', $request->input('country_doc_reg'))->first();
            if ($documentCountry?->is_italia) $required[] = 'city_doc_reg';
        }
        foreach ($required as $field) {
            $base[$field] = array_values(array_filter($base[$field], fn ($rule) => $rule !== 'nullable'));
            array_unshift($base[$field], 'required');
        }
        return $base;
    }

    private function validateSchedinaTransferMode($validator, Request $request): void
    {
        $saveMode = (string) $request->input('save_mode', $request->input('save_mode_intent', 'final'));
        if ($saveMode !== 'to_schedina') {
            return;
        }

        $tipoCliente = $this->normalizeTipoCliente($request->input('type_cliente', $request->input('type_housed')));
        if ($tipoCliente !== 'Ospite') {
            $validator->errors()->add('type_cliente', 'Per salvare in schedina il cliente deve essere di tipo Ospite.');
        }
    }

    private function validationAttributes(): array
    {
        return [
            'type_cliente' => 'tipo cliente',
            'name' => 'nome',
            'surname' => 'cognome',
            'group' => 'gruppo I',
            'subgroup' => 'gruppo II',
            'subgroup1' => 'gruppo III',
            'sex' => 'sesso',
            'country' => 'nazione residenza',
            'region' => 'regione residenza',
            'province' => 'provincia residenza',
            'city' => 'comune residenza',
            'typeaway' => 'tipo via residenza',
            'address' => 'indirizzo residenza',
            'number' => 'numero civico residenza',
            'phone' => 'telefono',
            'email' => 'email',
            'country_reg' => 'nazione anagrafica',
            'region_reg' => 'regione anagrafica',
            'prov_reg' => 'provincia anagrafica',
            'city_reg' => 'comune anagrafico',
            'privacy_consent' => 'consenso privacy',
            'marketing_consent' => 'consenso marketing',
            'communication_consent' => 'consenso comunicazioni',
            'ciudadania_reg' => 'cittadinanza',
            'nac_reg' => 'data di nascita',
            'type_doc_reg' => 'tipo documento',
            'num_doc_reg' => 'numero documento',
            'date_pub_reg' => 'data rilascio',
            'expire_reg' => 'scadenza documento',
            'rilasciato_reg' => 'rilasciato da',
            'country_doc_reg' => 'paese rilascio',
            'city_doc_reg' => 'città rilascio',
            'cf_az' => 'codice fiscale azienda',
            'pi_az' => 'partita IVA azienda',
            'sdi_az' => 'codice SDI',
            'email_az' => 'email azienda',
            'phone_az' => 'telefono azienda',
            'country_az' => 'nazione azienda',
            'city_az' => 'comune azienda',
        ];
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'Completa il campo :attribute.',
            'max.string' => 'Il campo :attribute non può superare :max caratteri.',
            'email.required_without_all' => 'Inserisci almeno un contatto del cliente: email, telefono o cellulare.',
            'email' => 'Inserisci un indirizzo email valido per :attribute.',
            'url' => 'Inserisci un sito web valido per :attribute.',
            'digits' => 'Il campo :attribute deve contenere esattamente :digits numeri.',
            'size.string' => 'Il campo :attribute deve contenere esattamente :size caratteri.',
            'regex' => 'Il formato del campo :attribute non e valido.',
            'in' => 'Seleziona un valore valido per :attribute.',
            'pi_az.digits' => 'La partita IVA azienda deve contenere esattamente 11 numeri.',
            'sdi_az.size' => 'Il codice SDI deve contenere esattamente 7 caratteri.',
            'sdi_az.regex' => 'Il codice SDI puo contenere solo lettere maiuscole e numeri.',
        ];
    }

    private function validateGruppiCascade($validator, Request $request, ?int $strutturaId): void
    {
        $old = $request->route('id') ? Customers::find($request->route('id')) : null;
        try {
            app(\App\Services\GruppiStrutturaService::class)->validateSelection(
                $request->only(['group', 'subgroup', 'subgroup1']),
                (int) ($strutturaId ?? StrutturaCorrente::getId() ?? auth()->user()?->struttura_id),
                $old?->only(['group', 'subgroup', 'subgroup1']) ?? []
            );
        } catch (\Illuminate\Validation\ValidationException $error) {
            foreach ($error->errors() as $field => $messages) foreach ($messages as $message) $validator->errors()->add($field, $message);
        }
    }

}
