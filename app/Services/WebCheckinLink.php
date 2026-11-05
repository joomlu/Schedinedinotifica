<?php

namespace App\Services;

use App\Models\WebCheckinRichiesta;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WebCheckinLink
{
    public function deadline(WebCheckinRichiesta $r): ?Carbon
    {
        foreach (['arrivo', 'partenza'] as $field) {
            $date = substr((string) ($r->getAttributes()[$field] ?? ''), 0, 10);
            if (! preg_match('/\A([0-9]{4})-([0-9]{2})-([0-9]{2})\z/', $date, $parts)
                || ! checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
                return null;
            }
        }
        if (! $r->arrivo || ! $r->partenza || $r->partenza->lt($r->arrivo)) {
            return null;
        }

        return Carbon::parse($r->arrivo->toDateString(), 'Europe/Rome')->startOfDay()->addDays(2);
    }

    public function valid(WebCheckinRichiesta $r): bool
    {
        $deadline = $r->link_expires_at ?? $this->deadline($r);

        return filled($r->token) && ! $r->link_revoked_at && $deadline && now()->lt($deadline)
            && $this->deadline($r) !== null;
    }

    public function revoke(WebCheckinRichiesta $r): void
    {
        $r->forceFill(['link_revoked_at' => $r->link_revoked_at ?? now()])->save();
    }

    public function issue(WebCheckinRichiesta $r): void
    {
        $deadline = $this->deadline($r);
        abort_unless($deadline, 422, 'Correggere le date previste prima di emettere il link.');
        $r->forceFill(['token' => Str::random(64), 'short_token' => Str::random(32),
            'link_issued_at' => now(), 'link_expires_at' => $deadline, 'link_revoked_at' => null])->save();
    }

    public function resolve(string $access): ?WebCheckinRichiesta
    {
        $query = WebCheckinRichiesta::query();
        $r = (clone $query)->where('token', $access)->first();
        if ($r && ! hash_equals((string) $r->token, $access)) {
            $r = null;
        }
        if (! $r) {
            $r = (clone $query)->where('short_token', $access)->first();
            if ($r && ! hash_equals((string) $r->short_token, $access)) {
                $r = null;
            }
        }
        if (! $r && preg_match('/\A([A-Za-z0-9]{1,30})-([A-Za-z0-9]{8})\z/', $access, $parts)) {
            $matches = $query->whereNull('short_token')->where('codice', $parts[1])
                ->where('token', 'like', $parts[2].'%')->limit(2)->get();
            if ($matches->count() === 1) {
                $candidate = $matches->first();
                if (preg_match('/\A[A-Za-z0-9]{64}\z/', (string) $candidate->token)
                    && hash_equals((string) $candidate->codice, $parts[1])
                    && hash_equals(substr((string) $candidate->token, 0, 8), $parts[2])) {
                    $r = $candidate;
                }
            }
        }

        return $r && $this->valid($r) ? $r : null;
    }
}
