<?php

namespace App\Http\Requests;

use App\Services\AnagraficaStruttura;
use App\Support\StrutturaAccess;
use Illuminate\Foundation\Http\FormRequest;

class StrutturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $s = StrutturaAccess::authorized($this);
        $u = $this->user();
        $master = ($u->isAdmin() || $u->isSuperAdmin()) && StrutturaAccess::query($u)->whereKey($s->id)->exists();
        if (! $master) {
            abort_unless($u->isProprietario() || $u->canManageGestioneOperativa($s->id), 403);
            $allowed = array_merge(AnagraficaStruttura::OPERATIVI, ['_token', '_method']);
            abort_if(array_diff(array_keys(array_merge($this->request->all(), $this->allFiles())), $allowed), 403, 'I dati maestri sono modificabili soltanto da Admin o Superadmin autorizzati.');
        } else {
            abort_if(array_intersect(array_keys($this->request->all()), AnagraficaStruttura::RISERVATI), 403);
        }

        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function domainData(): array
    {
        $s = StrutturaAccess::authorized($this);

        return app(AnagraficaStruttura::class)->validate($this, $s, false,
            ! (($this->user()->isAdmin() || $this->user()->isSuperAdmin()) && StrutturaAccess::query($this->user())->whereKey($s->id)->exists()));
    }
}
