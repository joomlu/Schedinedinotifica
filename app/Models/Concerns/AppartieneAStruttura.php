<?php

namespace App\Models\Concerns;

use App\Support\StrutturaCorrente;
use App\Models\Struttura;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait AppartieneAStruttura
{
    protected static function bootAppartieneAStruttura(): void
    {
        static::addGlobalScope('struttura', function (Builder $builder) {
            $user = Auth::user();
            $currentId = StrutturaCorrente::getId();
            $table = $builder->getModel()->getTable();

            $isSuper = $user && method_exists($user, 'isSuperAdmin') ? $user->isSuperAdmin() : false;
            $allowedIds = self::resolveAllowedStrutturaIds($user);

            if ($isSuper && $currentId === null) {
                return;
            }

            if ($currentId !== null) {
                if ($allowedIds === null) {
                    $builder->where($table . '.struttura_id', $currentId);
                    return;
                }

                if (in_array($currentId, $allowedIds, true)) {
                    $builder->where($table . '.struttura_id', $currentId);
                    return;
                }

                $builder->whereIn($table . '.struttura_id', $allowedIds ?: [-1]);
                return;
            }

            if ($allowedIds === null) {
                return;
            }

            if (!empty($allowedIds)) {
                $builder->whereIn($table . '.struttura_id', $allowedIds);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });

        static::creating(function ($model) {
            if (empty($model->struttura_id)) {
                $currentId = StrutturaCorrente::getId();
                if ($currentId === null && Auth::check()) {
                    $currentId = Auth::user()->struttura_id ?? null;
                }
                if ($currentId !== null) {
                    $model->struttura_id = $currentId;
                }
            }
        });
    }

    protected static function resolveAllowedStrutturaIds($user): ?array
    {
        if (!$user) {
            return [];
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return null; // null means no restriction
        }

        if (method_exists($user, 'isStrutturaUser') && $user->isStrutturaUser()) {
            return $user->struttura_id ? [$user->struttura_id] : [];
        }

        if (method_exists($user, 'isProprietario') && $user->isProprietario()) {
            if (!$user->proprietario_id) {
                return [];
            }

            if (!Schema::hasTable('proprietari')) {
                return [];
            }

            return Struttura::where('proprietario_id', $user->proprietario_id)->pluck('id')->all();
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            if (!Schema::hasTable('proprietari')) {
                return [];
            }
            return Struttura::where(function ($q) use ($user) {
                $q->whereHas('proprietario', function ($q2) use ($user) {
                    $q2->where('admin_id', $user->id);
                })->orWhereNull('proprietario_id');
            })->pluck('id')->all();
        }

        return [];
    }
}
