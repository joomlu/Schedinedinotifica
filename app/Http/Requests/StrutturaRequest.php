<?php

namespace App\Http\Requests;

use App\Services\AnagraficaStruttura;
use App\Support\StrutturaAccess;
use Illuminate\Foundation\Http\FormRequest;

class StrutturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $struttura = StrutturaAccess::authorized($this);
        abort_unless(StrutturaAccess::query($this->user())->whereKey($struttura->id)->exists(), 403);
        abort_if(array_intersect(
            array_keys(array_merge($this->request->all(), $this->allFiles())),
            AnagraficaStruttura::RISERVATI
        ), 403);

        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function domainData(): array
    {
        $s = StrutturaAccess::authorized($this);

        return app(AnagraficaStruttura::class)->validate($this, $s);
    }
}
