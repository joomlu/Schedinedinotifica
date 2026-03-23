<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TipoDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('tipo_documento')?->id ?? null;
        return [
            'codice' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tipo_documento', 'codice')->ignore($id),
            ],
            'descrizione' => ['required', 'string', 'max:191'],
        ];
    }
}
