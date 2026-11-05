<?php

namespace App\Services;

use App\Models\StrutturaAccesso;
use App\Models\User;
use Illuminate\Http\Request;

class AccessoOperativoService
{
    public function open(User $user, ?Request $request = null): void
    {
        if (!$user->struttura_id) {
            return;
        }

        StrutturaAccesso::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->whereNull('uscita_at')
            ->update([
                'uscita_at' => now(),
                'ip_uscita' => $request?->ip(),
                'updated_at' => now(),
            ]);

        $accesso = StrutturaAccesso::withoutGlobalScopes()->create([
            'struttura_id' => $user->struttura_id,
            'user_id' => $user->id,
            'entrata_at' => now(),
            'ip_entrata' => $request?->ip(),
            'user_agent' => substr((string) $request?->userAgent(), 0, 65000),
        ]);

        $user->forceFill([
            'ultimo_accesso_at' => now(),
            'ultimo_accesso_ip' => $request?->ip(),
        ])->save();

        if ($request) {
            $request->session()->put('accesso_operativo_id', $accesso->id);
        }
    }

    public function close(?User $user, ?Request $request = null): void
    {
        if (!$user || !$user->struttura_id) {
            return;
        }

        $accessoId = $request?->session()->get('accesso_operativo_id');

        $query = StrutturaAccesso::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->whereNull('uscita_at');

        if ($accessoId) {
            $query->where('id', $accessoId);
        }

        $query->latest('id')->limit(1)->get()->each(function (StrutturaAccesso $accesso) use ($request) {
            $accesso->forceFill([
                'uscita_at' => now(),
                'ip_uscita' => $request?->ip(),
            ])->save();
        });

        $user->forceFill([
            'ultima_uscita_at' => now(),
            'ultimo_logout_ip' => $request?->ip(),
        ])->save();

        if ($request) {
            $request->session()->forget('accesso_operativo_id');
        }
    }
}
