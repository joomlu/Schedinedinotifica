<?php

namespace App\Support;

use App\Models\Struttura;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class StrutturaAccess
{
    public const ATTRIBUTE = 'authorized_struttura';
    public const ORIGINAL_SELECTION = 'original_struttura_selection';

    public static function query(?User $user, bool $operational = false): Builder
    {
        $query = Struttura::query();
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        if ($user->isSuperAdmin()) {
            return $query;
        }
        if ($user->isAdmin()) {
            return $query->where(function (Builder $q) use ($user, $operational) {
                $q->whereHas('proprietario', fn (Builder $owner) => $owner->where('admin_id', $user->id));
                // Existing operational exception; never extend it to selector/CRUD.
                if ($operational) {
                    $q->orWhereNull('proprietario_id');
                }
            });
        }
        if ($user->isProprietario() && (int) $user->proprietario_id > 0) {
            return $query->where('proprietario_id', $user->proprietario_id);
        }
        if ($user->isStrutturaUser() && (int) $user->struttura_id > 0) {
            return $query->whereKey($user->struttura_id);
        }
        return $query->whereRaw('1 = 0');
    }

    public static function resolve(Request $request): Struttura
    {
        $user = $request->user();
        abort_unless($user && in_array($user->ruolo, ['super_admin', 'admin', 'proprietario', 'struttura_user'], true), 403);
        $ids = [];
        foreach (['struttura_id', 'sid'] as $key) {
            if ($request->query->has($key)) {
                $ids[] = self::id($request->query($key));
            }
        }
        abort_if(count(array_unique($ids)) > 1, 403);
        // Even a stale/forged session must not silently select a different target.
        $original = $request->attributes->get(self::ORIGINAL_SELECTION);
        if ($original !== null) {
            $sessionId = self::id($original);
            abort_unless(self::query($user, true)->whereKey($sessionId)->exists(), 403);
        }
        $selected = $ids[0] ?? StrutturaCorrente::getId();
        $query = self::query($user, true);
        $struttura = $selected !== null
            ? $query->whereKey(self::id($selected))->first()
            : $query->orderBy('id')->first();
        abort_unless($struttura, 403);
        return $struttura;
    }

    private static function id(mixed $value): int
    {
        abort_unless((is_int($value) || is_string($value))
            && preg_match('/^[1-9][0-9]*$/D', (string) $value)
            && filter_var($value, FILTER_VALIDATE_INT) !== false, 403);
        return (int) $value;
    }

    public static function authorized(Request $request): Struttura
    {
        $struttura = $request->attributes->get(self::ATTRIBUTE);
        abort_unless($struttura instanceof Struttura, 403);
        // Defense in depth: never trust an ID/object without current membership.
        abort_unless(self::query($request->user(), true)->whereKey($struttura->id)->exists(), 403);
        return $struttura;
    }
}
