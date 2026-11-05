<?php
namespace Tests\Support;
trait SchedinaPayloadFixture
{
    private function validSchedinaPayload(): array
    {
        return [
            'customer_privacy_consent' => '1',
            'name' => 'QA',
            'surname' => 'Schedina',
            'sex' => 'M',
            'arrive' => '2026-03-15',
            'departure' => '2026-03-17',
            'cant_people' => '1',
            'room' => '1',
            'beds' => '1',
            'oa_country' => 'ITALIA',
            'oa_region' => 'Lazio',
            'oa_prov' => 'RM',
            'oa_city' => 'Roma',
            'oa_city_nac' => 'ITALIANA',
            'oa_date_nac' => '1990-01-01',
            'or_country' => 'ITALIA',
            'or_region' => 'Lazio',
            'or_prov' => 'RM',
            'or_city' => 'Roma',
            'or_cap' => '00100',
            'or_typeaway' => 'Via',
            'or_address' => 'Via Test',
            'or_num' => '10',
            'or_doctype' => "CARTA DI IDENTITA'",
            'or_doc' => 'QATEST',
            'or_published_date' => '2025-01-01',
            'or_expire' => '2030-01-01',
            'or_published' => 'Comune di Roma',
            'or_published_country' => 'ITALIA',
            'or_published_city' => 'Roma',
        ];
    }
}
