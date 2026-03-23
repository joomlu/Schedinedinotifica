<?php

namespace App\Services;

use App\Models\Componenti;
use App\Models\LicenzaAssegnazione;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\StrutturaComanda;
use App\Models\SupportTicket;
use App\Support\StrutturaCorrente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NotificheService
{
    public function paginateForUser(User $user, string $status = '', string $search = '', int $page = 1, int $perPage = 20): array
    {
        $collection = $this->collectionForUser($user);
        $filtered = $this->filterCollection($collection, $status, $search)->values();

        $total = $filtered->count();
        $pageItems = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

        return [
            'items' => new LengthAwarePaginator(
                $pageItems,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            ),
            'contatori' => [
                'da_leggere' => $collection->where('stato', 'da_leggere')->count(),
                'aperte' => $collection->whereIn('stato', ['da_leggere', 'letta'])->count(),
                'chiuse' => $collection->where('stato', 'chiusa')->count(),
            ],
        ];
    }

    public function topbarForUser(User $user, int $limit = 5): array
    {
        $collection = $this->collectionForUser($user);

        return [
            'items' => $collection->take($limit)->values(),
            'non_lette' => $collection->where('stato', 'da_leggere')->count(),
        ];
    }

    private function collectionForUser(User $user): Collection
    {
        return $this->manualNotifications($user)
            ->concat($this->serviceNotifications($user))
            ->concat($this->supportNotifications($user))
            ->concat($this->birthdayNotifications($user))
            ->sort(function ($a, $b) {
                $statusOrder = ['da_leggere' => 0, 'letta' => 1, 'chiusa' => 2];
                $statusCompare = ($statusOrder[$a->stato] ?? 9) <=> ($statusOrder[$b->stato] ?? 9);
                if ($statusCompare !== 0) {
                    return $statusCompare;
                }

                return optional($b->created_at)->getTimestamp() <=> optional($a->created_at)->getTimestamp();
            })
            ->values();
    }

    private function manualNotifications(User $user): Collection
    {
        $strutturaId = $this->resolveStrutturaId($user);
        if (!$strutturaId) {
            return collect();
        }

        return StrutturaComanda::query()
            ->with(['mittente', 'destinatario'])
            ->visibleForUser($user)
            ->get()
            ->map(function (StrutturaComanda $notifica) {
                $notifica->notification_kind = 'manuale';
                $notifica->mittente_label = $notifica->mittente?->displayLabel() ?? 'Sistema';
                $notifica->destinatario_label = $notifica->destinatario?->displayLabel() ?? 'Turno successivo';
                $notifica->can_mark = $notifica->stato === 'da_leggere';
                $notifica->can_close = $notifica->stato !== 'chiusa';
                $notifica->detail_link = null;
                $notifica->detail_link_label = null;
                return $notifica;
            });
    }

    private function birthdayNotifications(User $user): Collection
    {
        $today = Carbon::today();
        $strutturaId = $this->resolveStrutturaId($user);
        if (!$strutturaId) {
            return collect();
        }

        $schedine = Schedina::query()
            ->withoutGlobalScopes()
            ->where('struttura_id', $strutturaId)
            ->where(function ($query) {
                $query->where('circuito', 'schedina')
                    ->orWhere('circuito', 'arrivi')
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('circuito')->where('is_arrive', 0);
                    });
            })
            ->whereDate('arrive', '<=', $today->toDateString())
            ->whereDate('departure', '>=', $today->toDateString())
            ->with('componenti')
            ->get();

        $items = collect();

        foreach ($schedine as $schedina) {
            $items = $items->concat($this->birthdayRowsForSchedina($schedina, $today));
        }

        return $items;
    }

    private function serviceNotifications(User $user): Collection
    {
        $strutturaId = $this->resolveStrutturaId($user);
        if (!$strutturaId) {
            return collect();
        }

        $struttura = Struttura::query()
            ->with(['proprietario.admin'])
            ->find($strutturaId);

        if (!$struttura) {
            return collect();
        }

        $items = collect();
        $now = Carbon::now();

        if (!$struttura->attiva && (!$struttura->scadenza_servizio || !$struttura->scadenza_servizio->isPast())) {
            $items->push($this->makeSystemNotification(
                id: 'servizio-offline-'.$struttura->id,
                titolo: 'Servizio struttura offline',
                messaggio: "La struttura {$struttura->nome_struttura} risulta disattivata. Riattivala o aggiorna il contratto/licenza per riprendere l'operativita.",
                priorita: 'alta',
                createdAt: $now->copy()->subMinutes(10),
                detailLink: $this->serviceDetailLink($user, $struttura->id),
                detailLabel: 'Apri relazione e pagamenti'
            ));
        }

        if ($struttura->scadenza_servizio) {
            if ($struttura->scadenza_servizio->isPast()) {
                $items->push($this->makeSystemNotification(
                    id: 'servizio-scaduto-'.$struttura->id,
                    titolo: 'Servizio scaduto',
                    messaggio: "La struttura {$struttura->nome_struttura} e scaduta il {$struttura->scadenza_servizio->format('d/m/Y')}. Verifica pagamenti, rinnovo e stato della licenza.",
                    priorita: 'alta',
                    createdAt: $now->copy()->subMinutes(5),
                    detailLink: $this->serviceDetailLink($user, $struttura->id),
                    detailLabel: 'Apri relazione e pagamenti'
                ));
            } elseif ($struttura->scadenza_servizio->diffInDays($now) <= 30) {
                $items->push($this->makeSystemNotification(
                    id: 'servizio-in-scadenza-'.$struttura->id,
                    titolo: 'Servizio in scadenza',
                    messaggio: "La struttura {$struttura->nome_struttura} scade il {$struttura->scadenza_servizio->format('d/m/Y')}. Conviene avvisare admin e proprietario prima che vada offline.",
                    priorita: 'normale',
                    createdAt: $now->copy()->subMinutes(1),
                    detailLink: $this->serviceDetailLink($user, $struttura->id),
                    detailLabel: 'Apri relazione e pagamenti'
                ));
            }
        }

        $licenzeCritiche = LicenzaAssegnazione::query()
            ->where('struttura_id', $struttura->id)
            ->where(function ($query) {
                $query->whereIn('stato_pagamento', ['da_pagare', 'sospeso'])
                    ->orWhere(function ($inner) {
                        $inner->whereNotNull('data_scadenza')
                            ->whereDate('data_scadenza', '<', now()->toDateString());
                    });
            })
            ->count();

        if ($licenzeCritiche > 0) {
            $items->push($this->makeSystemNotification(
                id: 'licenze-critiche-'.$struttura->id,
                titolo: 'Licenze con attenzione richiesta',
                messaggio: "La struttura {$struttura->nome_struttura} ha {$licenzeCritiche} licenze con pagamento aperto, sospeso o gia scaduto.",
                priorita: 'normale',
                createdAt: $now,
                detailLink: $this->licenseDetailLink($user, $struttura->id),
                detailLabel: 'Controlla licenze'
            ));
        }

        return $items;
    }

    private function supportNotifications(User $user): Collection
    {
        if ($user->isSuperAdmin()) {
            $tickets = SupportTicket::query()
                ->with(['struttura', 'assignedAdmin'])
                ->where(function ($query) {
                    $query->unreadForAdmin()
                        ->orWhere('priorita', 'urgente');
                })
                ->latest('ultimo_messaggio_at')
                ->limit(10)
                ->get();
        } elseif ($user->isAdmin()) {
            $allowedIds = Struttura::query()
                ->whereHas('proprietario', fn ($inner) => $inner->where('admin_id', $user->id))
                ->pluck('id')
                ->all();

            $tickets = SupportTicket::query()
                ->with(['struttura', 'assignedAdmin'])
                ->whereIn('struttura_id', $allowedIds ?: [-1])
                ->where(function ($query) {
                    $query->unreadForAdmin()
                        ->orWhere('priorita', 'urgente');
                })
                ->latest('ultimo_messaggio_at')
                ->limit(10)
                ->get();
        } else {
            $strutturaId = $this->resolveStrutturaId($user);
            if (!$strutturaId) {
                return collect();
            }

            $tickets = SupportTicket::query()
                ->with(['struttura', 'assignedAdmin'])
                ->where('struttura_id', $strutturaId)
                ->where(function ($query) {
                    $query->unreadForStruttura()
                        ->orWhere('priorita', 'urgente');
                })
                ->latest('ultimo_messaggio_at')
                ->limit(10)
                ->get();
        }

        return $tickets->map(function (SupportTicket $ticket) use ($user) {
            return $this->makeSystemNotification(
                id: 'support-ticket-' . $ticket->id,
                titolo: 'Supporto: ' . $ticket->titolo,
                messaggio: 'Ticket ' . $ticket->ticket_code . ' - ' . ($ticket->struttura?->nome_struttura ?: 'Struttura') . ' - stato ' . $ticket->statoLabel() . '.',
                priorita: $ticket->priorita === 'urgente' ? 'alta' : 'normale',
                createdAt: $ticket->ultimo_messaggio_at ?: $ticket->updated_at ?: now(),
                detailLink: route('supporto.show', $ticket->id),
                detailLabel: 'Apri ticket'
            );
        });
    }

    private function birthdayRowsForSchedina(Schedina $schedina, Carbon $today): Collection
    {
        $rows = collect();

        $rows = $rows->concat($this->makeBirthdayNotification(
            birthDate: $schedina->oa_date_nac,
            fullName: trim(($schedina->name ?? '') . ' ' . ($schedina->surname ?? '')),
            schedina: $schedina,
            suffix: 'main',
            today: $today
        ));

        foreach ($schedina->componenti as $componente) {
            $rows = $rows->concat($this->makeBirthdayNotification(
                birthDate: $componente->date_nac,
                fullName: trim(($componente->name ?? '') . ' ' . ($componente->surname ?? '')),
                schedina: $schedina,
                suffix: 'comp-' . $componente->id,
                today: $today
            ));
        }

        return $rows;
    }

    private function makeBirthdayNotification($birthDate, string $fullName, Schedina $schedina, string $suffix, Carbon $today): Collection
    {
        $fullName = trim($fullName);
        if ($fullName === '' || blank($birthDate)) {
            return collect();
        }

        try {
            $birthday = Carbon::parse((string) $birthDate);
        } catch (\Throwable $e) {
            return collect();
        }

        if ($birthday->format('m-d') !== $today->format('m-d')) {
            return collect();
        }

        $age = $birthday->diffInYears($today);
        $origine = $this->origineScheda($schedina);
        $arrivo = $this->formatDateLabel($schedina->arrive);
        $partenza = $this->formatDateLabel($schedina->departure);
        $message = "Oggi {$fullName} compie {$age} anni ed è presente in struttura."
            . ($schedina->scheda ? " Scheda {$schedina->scheda}." : '')
            . ($arrivo ? " Arrivo: {$arrivo}." : '')
            . ($partenza ? " Partenza: {$partenza}." : '')
            . " Origine operativa: {$origine}.";

        return collect([(object) [
            'id' => 'birthday-' . $schedina->id . '-' . $suffix,
            'notification_kind' => 'compleanno',
            'titolo' => 'Compleanno in casa: ' . $fullName,
            'messaggio' => $message,
            'mittente' => null,
            'destinatario' => null,
            'mittente_label' => 'Sistema',
            'destinatario_label' => 'Reception',
            'priorita' => 'normale',
            'stato' => 'da_leggere',
            'created_at' => $today->copy()->setTime(8, 0),
            'letto_at' => null,
            'chiuso_at' => null,
            'can_mark' => false,
            'can_close' => false,
            'detail_link' => route('schedina.edit', ['id' => $schedina->id]),
            'detail_link_label' => 'Apri scheda',
            'scheda' => $schedina->scheda,
            'origine' => $origine,
            'arrivo' => $schedina->arrive,
            'partenza' => $schedina->departure,
        ]]);
    }

    private function filterCollection(Collection $collection, string $status, string $search): Collection
    {
        return $collection
            ->when(in_array($status, ['da_leggere', 'letta', 'chiusa'], true), fn (Collection $rows) => $rows->where('stato', $status))
            ->when($search !== '', function (Collection $rows) use ($search) {
                $needle = mb_strtolower($search);

                return $rows->filter(function ($row) use ($needle) {
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        (string) ($row->titolo ?? ''),
                        (string) ($row->messaggio ?? ''),
                        (string) ($row->scheda ?? ''),
                        (string) ($row->origine ?? ''),
                    ])));

                    return str_contains($haystack, $needle);
                });
            });
    }

    private function origineScheda(Schedina $schedina): string
    {
        $circuito = strtolower(trim((string) ($schedina->circuito ?? '')));

        if ($circuito === 'web') {
            return 'Web Check-in';
        }

        if ($circuito === 'arrivi' || (!$circuito && (bool) ($schedina->is_arrive ?? false))) {
            return 'Arrivi';
        }

        return 'Schedine';
    }

    private function formatDateLabel($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveStrutturaId(User $user): ?int
    {
        return $user->struttura_id ?: StrutturaCorrente::getId();
    }

    private function serviceDetailLink(User $user, int $strutturaId): string
    {
        return match ($user->ruolo) {
            'super_admin' => route('superadmin.strutture.edit', ['id' => $strutturaId, 'tab' => 'relazione']),
            'admin' => route('admin.strutture.edit', ['id' => $strutturaId, 'tab' => 'relazione']),
            default => route('struttura.edit', ['tab' => 'relazione']),
        };
    }

    private function licenseDetailLink(User $user, int $strutturaId): string
    {
        return match ($user->ruolo) {
            'super_admin' => route('superadmin.strutture.edit', ['id' => $strutturaId, 'tab' => 'licenze']),
            'admin' => route('admin.strutture.edit', ['id' => $strutturaId, 'tab' => 'licenze']),
            default => route('struttura.edit', ['tab' => 'licenze']),
        };
    }

    private function makeSystemNotification(
        string $id,
        string $titolo,
        string $messaggio,
        string $priorita,
        Carbon $createdAt,
        ?string $detailLink = null,
        ?string $detailLabel = null
    ): object {
        return (object) [
            'id' => $id,
            'notification_kind' => 'sistema',
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'mittente' => null,
            'destinatario' => null,
            'mittente_label' => 'Sistema',
            'destinatario_label' => 'Gestione',
            'priorita' => $priorita,
            'stato' => 'da_leggere',
            'created_at' => $createdAt,
            'letto_at' => null,
            'chiuso_at' => null,
            'can_mark' => false,
            'can_close' => false,
            'detail_link' => $detailLink,
            'detail_link_label' => $detailLabel,
        ];
    }
}
