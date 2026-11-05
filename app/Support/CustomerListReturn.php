<?php

namespace App\Support;

final class CustomerListReturn
{
    public static function url(): string
    {
        $sid = StrutturaCorrente::getId();
        $export = session('clienti_origine.'.$sid) === 'export';
        $filters = session(($export ? 'clienti_export.' : 'clienti_elenco.').$sid, []);
        if ($export) $filters = array_intersect_key($filters, array_flip(['q', 'tipo_cliente', 'page']));
        return route($export ? 'customer.export.index' : 'customers', $filters);
    }
}
