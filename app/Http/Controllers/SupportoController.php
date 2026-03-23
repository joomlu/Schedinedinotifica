<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\LicenzaAssegnazione;
use App\Models\Struttura;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupportoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();
        $isSuperAdmin = $user->isSuperAdmin();
        $isBackoffice = $isAdmin || $isSuperAdmin;
        $struttura = $this->resolveStruttura($request, $isBackoffice);

        $filters = [
            'stato' => trim((string) $request->query('stato', '')),
            'priorita' => trim((string) $request->query('priorita', '')),
            'q' => trim((string) $request->query('q', '')),
            'struttura_id' => (int) $request->query('struttura_id', 0),
        ];

        $query = SupportTicket::query()
            ->with(['struttura', 'openedBy', 'assignedAdmin']);

        if ($isSuperAdmin) {
            if ($filters['struttura_id'] > 0) {
                $query->where('struttura_id', $filters['struttura_id']);
            }
        } elseif ($isAdmin) {
            $allowedIds = Struttura::query()
                ->whereHas('proprietario', fn ($inner) => $inner->where('admin_id', $user->id))
                ->pluck('id')
                ->all();

            $query->whereIn('struttura_id', $allowedIds ?: [-1]);

            if ($filters['struttura_id'] > 0) {
                $query->where('struttura_id', $filters['struttura_id']);
            }
        } else {
            $query->where('struttura_id', $struttura->id);
        }

        if (array_key_exists($filters['stato'], SupportTicket::STATI)) {
            $query->where('stato', $filters['stato']);
        }

        if (array_key_exists($filters['priorita'], SupportTicket::PRIORITA)) {
            $query->where('priorita', $filters['priorita']);
        }

        if ($filters['q'] !== '') {
            $query->where(function ($inner) use ($filters) {
                $inner->where('ticket_code', 'like', '%' . $filters['q'] . '%')
                    ->orWhere('titolo', 'like', '%' . $filters['q'] . '%')
                    ->orWhere('descrizione', 'like', '%' . $filters['q'] . '%');
            });
        }

        $tickets = $query
            ->orderByRaw("case when stato = 'aperto' then 0 when stato = 'in_lavorazione' then 1 when stato = 'in_attesa_struttura' then 2 else 3 end")
            ->orderByRaw("case when priorita = 'urgente' then 0 when priorita = 'alta' then 1 when priorita = 'normale' then 2 else 3 end")
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $struttureFiltro = $isBackoffice
            ? $this->resolveSupportStruttureFiltro($user, $isSuperAdmin)
            : collect();

        return view('supporto.index', [
            'isAdmin' => $isBackoffice,
            'isSuperAdmin' => $isSuperAdmin,
            'struttura' => $struttura,
            'tickets' => $tickets,
            'filters' => $filters,
            'struttureFiltro' => $struttureFiltro,
            'contatori' => $this->buildCounters($user, $isBackoffice, $isSuperAdmin, $struttura?->id),
            'categorie' => SupportTicket::CATEGORIE,
            'priorita' => SupportTicket::PRIORITA,
            'stati' => SupportTicket::STATI,
        ]);
    }

    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
        $struttura = $this->resolveStruttura($request, $isAdmin);

        $ticket = SupportTicket::query()
            ->with(['struttura', 'openedBy', 'assignedAdmin', 'messages.author'])
            ->findOrFail($id);

        if (!$isAdmin) {
            abort_unless($ticket->struttura_id === $struttura->id, 403);
        }

        if ($user->isSuperAdmin()) {
            $ticket->forceFill(['last_admin_read_at' => now()])->save();
        } elseif ($user->isAdmin()) {
            $this->guardAdminTicketAccess($user, $ticket);
            $ticket->forceFill(['last_admin_read_at' => now()])->save();
        } else {
            $ticket->forceFill(['last_struttura_read_at' => now()])->save();
        }

        return view('supporto.show', [
            'ticket' => $ticket,
            'isAdmin' => $isAdmin,
            'categorie' => SupportTicket::CATEGORIE,
            'priorita' => SupportTicket::PRIORITA,
            'stati' => SupportTicket::STATI,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $struttura = $this->resolveStruttura($request, false);

        $data = $request->validate([
            'titolo' => ['required', 'string', 'max:180'],
            'categoria' => ['required', Rule::in(array_keys(SupportTicket::CATEGORIE))],
            'priorita' => ['required', Rule::in(array_keys(SupportTicket::PRIORITA))],
            'modulo_riferimento' => ['nullable', 'string', 'max:80'],
            'descrizione' => ['required', 'string', 'max:10000'],
        ], [
            'titolo.required' => 'Inserisci il titolo del ticket.',
            'categoria.required' => 'Seleziona la categoria.',
            'priorita.required' => 'Seleziona la priorita.',
            'descrizione.required' => 'Scrivi la richiesta di supporto.',
        ]);

        $ticket = DB::transaction(function () use ($data, $user, $struttura) {
            $ticket = SupportTicket::create([
                'struttura_id' => $struttura->id,
                'opened_by_user_id' => $user->id,
                'ticket_code' => $this->nextTicketCode(),
                'titolo' => $data['titolo'],
                'categoria' => $data['categoria'],
                'priorita' => $data['priorita'],
                'stato' => 'aperto',
                'modulo_riferimento' => $data['modulo_riferimento'] ?: null,
                'descrizione' => $data['descrizione'],
                'ultimo_messaggio_at' => now(),
                'ultimo_messaggio_da' => 'struttura',
                'last_struttura_read_at' => now(),
            ]);

            SupportTicketMessage::create([
                'support_ticket_id' => $ticket->id,
                'struttura_id' => $struttura->id,
                'author_user_id' => $user->id,
                'messaggio' => $data['descrizione'],
            ]);

            return $ticket;
        });

        return redirect()->route('supporto.show', $ticket->id)->with('success', 'Ticket di supporto creato.');
    }

    public function reply(Request $request, int $id)
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
        $struttura = $this->resolveStruttura($request, $isAdmin);

        $ticket = SupportTicket::query()->findOrFail($id);
        if (!$isAdmin) {
            abort_unless($ticket->struttura_id === $struttura->id, 403);
        } elseif ($user->isAdmin()) {
            $this->guardAdminTicketAccess($user, $ticket);
        }

        $data = $request->validate([
            'messaggio' => ['required', 'string', 'max:10000'],
        ], [
            'messaggio.required' => 'Scrivi la risposta del ticket.',
        ]);

        DB::transaction(function () use ($ticket, $user, $data, $isAdmin) {
            SupportTicketMessage::create([
                'support_ticket_id' => $ticket->id,
                'struttura_id' => $ticket->struttura_id,
                'author_user_id' => $user->id,
                'messaggio' => $data['messaggio'],
            ]);

            $ticket->forceFill([
                'assigned_admin_id' => $isAdmin ? ($ticket->assigned_admin_id ?: $user->id) : $ticket->assigned_admin_id,
                'ultimo_messaggio_at' => now(),
                'ultimo_messaggio_da' => $isAdmin ? 'admin' : 'struttura',
                'last_admin_read_at' => $isAdmin ? now() : $ticket->last_admin_read_at,
                'last_struttura_read_at' => $isAdmin ? $ticket->last_struttura_read_at : now(),
                'stato' => $isAdmin ? 'in_attesa_struttura' : 'aperto',
            ])->save();
        });

        return redirect()->route('supporto.show', $ticket->id)->with('success', 'Risposta salvata.');
    }

    public function updateStatus(Request $request, int $id)
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
        $struttura = $this->resolveStruttura($request, $isAdmin);

        $ticket = SupportTicket::query()->findOrFail($id);
        if (!$isAdmin) {
            abort_unless($ticket->struttura_id === $struttura->id, 403);
        } elseif ($user->isAdmin()) {
            $this->guardAdminTicketAccess($user, $ticket);
        }

        $allowed = $isAdmin
            ? ['aperto', 'in_lavorazione', 'in_attesa_struttura', 'chiuso']
            : ['chiuso', 'aperto'];

        $data = $request->validate([
            'stato' => ['required', Rule::in($allowed)],
        ]);

        $ticket->forceFill([
            'stato' => $data['stato'],
            'chiuso_at' => $data['stato'] === 'chiuso' ? now() : null,
        ])->save();

        return redirect()->route('supporto.show', $ticket->id)->with('success', 'Stato ticket aggiornato.');
    }

    public function assign(Request $request, int $id)
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $user->isSuperAdmin(), 403);

        $ticket = SupportTicket::query()->findOrFail($id);
        if ($user->isAdmin()) {
            $this->guardAdminTicketAccess($user, $ticket);
        }
        $ticket->forceFill([
            'assigned_admin_id' => $user->id,
            'stato' => 'in_lavorazione',
        ])->save();

        return redirect()->route('supporto.show', $ticket->id)->with('success', 'Ticket assegnato.');
    }

    private function resolveStruttura(Request $request, bool $allowAdminAll): ?Struttura
    {
        $user = $request->user();

        if ($user->struttura_id) {
            StrutturaCorrente::setId($user->struttura_id);
            return Struttura::query()->find($user->struttura_id);
        }

        $strutturaId = StrutturaCorrente::getId();
        if ($strutturaId) {
            return Struttura::query()->find($strutturaId);
        }

        if ($allowAdminAll) {
            return null;
        }

        abort(403, 'Struttura non selezionata.');
    }

    private function buildCounters(User $user, bool $isBackoffice, bool $isSuperAdmin, ?int $strutturaId): array
    {
        $base = SupportTicket::query();
        if ($isSuperAdmin) {
            // no restriction
        } elseif ($isBackoffice) {
            $allowedIds = Struttura::query()
                ->whereHas('proprietario', fn ($inner) => $inner->where('admin_id', $user->id))
                ->pluck('id')
                ->all();
            $base->whereIn('struttura_id', $allowedIds ?: [-1]);
        } elseif ($strutturaId) {
            $base->where('struttura_id', $strutturaId);
        }

        return [
            'aperti' => (clone $base)->whereIn('stato', ['aperto', 'in_lavorazione'])->count(),
            'attesa' => (clone $base)->where('stato', 'in_attesa_struttura')->count(),
            'chiusi' => (clone $base)->where('stato', 'chiuso')->count(),
            'urgenti' => (clone $base)->where('priorita', 'urgente')->count(),
            'non_letti' => $isBackoffice
                ? (clone $base)->unreadForAdmin()->count()
                : (clone $base)->unreadForStruttura()->count(),
            'assegnati_a_me' => $isBackoffice
                ? (clone $base)->where('assigned_admin_id', auth()->id())->where('stato', '!=', 'chiuso')->count()
                : 0,
            'servizi_in_scadenza' => $this->countServiziInScadenza($user, $isBackoffice, $isSuperAdmin, $strutturaId),
            'strutture_offline' => $this->countStruttureOffline($user, $isBackoffice, $isSuperAdmin, $strutturaId),
            'licenze_da_pagare' => $this->countLicenzeDaPagare($user, $isBackoffice, $isSuperAdmin, $strutturaId),
        ];
    }

    private function countServiziInScadenza(User $user, bool $isBackoffice, bool $isSuperAdmin, ?int $strutturaId): int
    {
        $query = Struttura::query()->whereNotNull('scadenza_servizio');

        if ($isSuperAdmin) {
            // no restriction
        } elseif ($isBackoffice) {
            $allowedIds = Struttura::query()
                ->whereHas('proprietario', fn ($inner) => $inner->where('admin_id', $user->id))
                ->pluck('id')
                ->all();
            $query->whereIn('id', $allowedIds ?: [-1]);
        } elseif ($strutturaId) {
            $query->where('id', $strutturaId);
        } else {
            return 0;
        }

        return $query
            ->whereBetween('scadenza_servizio', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->count();
    }

    private function countLicenzeDaPagare(User $user, bool $isBackoffice, bool $isSuperAdmin, ?int $strutturaId): int
    {
        $query = LicenzaAssegnazione::query()->where('stato_pagamento', 'da_pagare');

        if ($isSuperAdmin) {
            // no restriction
        } elseif ($isBackoffice) {
            $allowedIds = Struttura::query()
                ->whereHas('proprietario', fn ($inner) => $inner->where('admin_id', $user->id))
                ->pluck('id')
                ->all();
            $query->whereIn('struttura_id', $allowedIds ?: [-1]);
        } elseif ($strutturaId) {
            $query->where('struttura_id', $strutturaId);
        } else {
            return 0;
        }

        return $query->count();
    }

    private function countStruttureOffline(User $user, bool $isBackoffice, bool $isSuperAdmin, ?int $strutturaId): int
    {
        $query = Struttura::query();

        if ($isSuperAdmin) {
            // no restriction
        } elseif ($isBackoffice) {
            $allowedIds = Struttura::query()
                ->whereHas('proprietario', fn ($inner) => $inner->where('admin_id', $user->id))
                ->pluck('id')
                ->all();
            $query->whereIn('id', $allowedIds ?: [-1]);
        } elseif ($strutturaId) {
            $query->where('id', $strutturaId);
        } else {
            return 0;
        }

        return $query
            ->where(function ($inner) {
                $inner->where('attiva', false)
                    ->orWhere(function ($expired) {
                        $expired->whereNotNull('scadenza_servizio')
                            ->whereDate('scadenza_servizio', '<', now()->toDateString());
                    });
            })
            ->count();
    }

    private function resolveSupportStruttureFiltro($user, bool $isSuperAdmin)
    {
        $query = Struttura::query()->orderBy('nome_struttura');

        if (!$isSuperAdmin) {
            $query->whereHas('proprietario', fn ($inner) => $inner->where('admin_id', $user->id));
        }

        return $query->get(['id', 'nome_struttura']);
    }

    private function guardAdminTicketAccess($user, SupportTicket $ticket): void
    {
        $allowedIds = Struttura::query()
            ->whereHas('proprietario', fn ($inner) => $inner->where('admin_id', $user->id))
            ->pluck('id')
            ->all();

        abort_unless(in_array((int) $ticket->struttura_id, $allowedIds, true), 403);
    }

    private function nextTicketCode(): string
    {
        $year = now()->format('y');
        $last = SupportTicket::withoutGlobalScopes()
            ->where('ticket_code', 'like', 'SUP-' . $year . '%')
            ->orderByDesc('ticket_code')
            ->value('ticket_code');

        $next = 1;
        if ($last && preg_match('/^SUP-\d{2}(\d{4})$/', $last, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return sprintf('SUP-%s%04d', $year, $next);
    }
}
