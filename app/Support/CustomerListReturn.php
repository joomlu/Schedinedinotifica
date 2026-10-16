<?php

namespace App\Support;

final class CustomerListReturn
{
    public static function url(): string
    {
        $sid = StrutturaCorrente::getId();
        $export = session('clienti_origine.'.$sid) === 'export';
        return route($export ? 'customer.export.index' : 'customers',
            session(($export ? 'clienti_export.' : 'clienti_elenco.').$sid, []));
    }
}
