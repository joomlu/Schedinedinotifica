<?php

namespace App\Http\Controllers;

use App\Models\CalendarioEvento;
use App\Models\Componenti;
use App\Models\Customers;
use App\Models\LicenzaAssegnazione;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\User;
use App\Support\StrutturaCorrente;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CalendarioController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $calendar = $this->resolveCalendarContext($request);
        $struttura = $calendar['struttura'];
        $contesto = $calendar['contesto'];
        $struttureDisponibili = $calendar['strutture_disponibili'];
        $canSelectStructure = $calendar['can_select_structure'];
        $allowedStructureIds = $calendar['allowed_structure_ids'];
        $portfolioLabel = $calendar['portfolio_label'];
        $scopeStructures = $contesto === 'portfolio'
            ? ($struttura ? collect([$struttura]) : $struttureDisponibili)
            : $struttureDisponibili;
        $scopeStructureIds = $contesto === 'portfolio'
            ? $scopeStructures->pluck('id')->map(fn ($id) => (int) $id)->all()
            : $allowedStructureIds;

        $month = $this->parseMonth($request->query('mese'), $request->query('giorno'));
        $selectedDay = $this->parseDay($request->query('giorno'), $month);
        $vista = (string) $request->query('vista', ($request->has('giorno') ? 'day' : 'month'));
        $statoStorico = (string) $request->query('stato_storico', '');
        $q = trim((string) $request->query('q', ''));

        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $manualMonthEvents = $this->manualEventsQuery($user, $contesto, $struttura?->id, $scopeStructureIds)
            ->with(['creator', 'closer', 'owner', 'struttura'])
            ->whereBetween('data_evento', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->orderBy('data_evento')
            ->orderBy('ora_evento')
            ->orderByDesc('id')
            ->get();

        $automaticMonthEvents = collect();

        if ($contesto === 'struttura' && $struttura) {
            $automaticMonthEvents = collect($this->automaticEvents($struttura, $monthStart, $monthEnd));
        } elseif ($contesto === 'portfolio') {
            $automaticMonthEvents = $scopeStructures
                ->flatMap(fn (Struttura $item) => $this->automaticEvents($item, $monthStart, $monthEnd))
                ->values();
        }

        $allMonthEvents = $manualMonthEvents
            ->map(fn (CalendarioEvento $evento) => $this->presentManualEvent($evento))
            ->concat($automaticMonthEvents)
            ->filter(fn (array $event) => $this->calendarSearchMatches($event, $q))
            ->sortBy([['data_evento', 'asc'], ['sort_time', 'asc'], ['titolo', 'asc']])
            ->values();

        $eventsByDay = $allMonthEvents->groupBy('date_key');

        $agendaEvents = collect();

        if ($contesto === 'struttura' && $struttura) {
            $agendaEvents = $agendaEvents->concat($this->automaticEvents($struttura, $selectedDay, $selectedDay));
        } elseif ($contesto === 'portfolio') {
            $agendaEvents = $agendaEvents->concat(
                $struttureDisponibili->flatMap(fn (Struttura $item) => $this->automaticEvents($item, $selectedDay, $selectedDay))
            );
        }

        $agendaEvents = $agendaEvents
            ->concat(
                $this->manualEventsQuery($user, $contesto, $struttura?->id, $scopeStructureIds)
                    ->with(['creator', 'closer', 'owner', 'struttura'])
                    ->whereDate('data_evento', $selectedDay->toDateString())
                    ->orderBy('ora_evento')
                    ->orderByDesc('id')
                    ->get()
                    ->map(fn (CalendarioEvento $evento) => $this->presentManualEvent($evento))
            )
            ->filter(fn (array $event) => $this->calendarSearchMatches($event, $q))
            ->sortBy([['sort_time', 'asc'], ['titolo', 'asc']])
            ->values();

        $history = $this->manualEventsQuery($user, $contesto, $struttura?->id, $scopeStructureIds)
            ->with(['creator', 'closer', 'owner', 'struttura'])
            ->where(function ($query) use ($selectedDay) {
                $query->whereIn('stato', ['completata', 'chiusa'])
                    ->orWhereDate('data_evento', '<', $selectedDay->toDateString());
            })
            ->when($statoStorico !== '', fn ($query) => $query->where('stato', $statoStorico))
            ->orderByDesc('data_evento')
            ->orderByDesc('ora_evento')
            ->limit(80)
            ->get();

        if ($q !== '') {
            $history = $history->filter(function (CalendarioEvento $evento) use ($q) {
                $haystack = mb_strtolower(implode(' ', array_filter([
                    $evento->titolo,
                    $evento->descrizione,
                    $evento->creator?->displayLabel(),
                    $evento->struttura?->nome_struttura,
                    $evento->tipo,
                    $evento->stato,
                ])));

                return str_contains($haystack, mb_strtolower($q));
            })->values();
        }

        $utenti = $contesto === 'struttura' && $struttura
            ? User::query()
                ->where('struttura_id', $struttura->id)
                ->orderByRaw("case when ruolo_operativo = 'proprietario' then 0 else 1 end")
                ->orderBy('display_name')
                ->orderBy('name')
                ->get()
            : collect();

        $contatori = [
            'manuali_aperte' => $this->manualEventsQuery($user, $contesto, $struttura?->id, $scopeStructureIds)->whereIn('stato', ['da_fare', 'vista'])->count(),
            'oggi' => $agendaEvents->count(),
            'compleanni' => $automaticMonthEvents->where('tipo', 'compleanno')->count(),
            'movimenti' => $automaticMonthEvents->whereIn('tipo', ['checkin', 'checkout'])->count(),
        ];

        $calendarWeeks = collect(CarbonPeriod::create($gridStart, '1 day', $gridEnd))
            ->chunk(7)
            ->values();

        return view('calendario.index', [
            'struttura' => $struttura,
            'month' => $month,
            'selectedDay' => $selectedDay,
            'activeTab' => $vista,
            'vista' => $vista,
            'calendarWeeks' => $calendarWeeks,
            'eventsByDay' => $eventsByDay,
            'agendaEvents' => $agendaEvents,
            'history' => $history,
            'manualMonthEvents' => $manualMonthEvents,
            'utenti' => $utenti,
            'contatori' => $contatori,
            'statoStorico' => $statoStorico,
            'q' => $q,
            'prevMonth' => $month->copy()->subMonthNoOverflow()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonthNoOverflow()->format('Y-m'),
            'contesto' => $contesto,
            'hasStrutturaContext' => (bool) $struttura,
            'canSelectStructure' => $canSelectStructure,
            'struttureDisponibili' => $struttureDisponibili,
            'selectedStructureId' => $struttura?->id,
            'portfolioLabel' => $portfolioLabel,
        ]);
    }

    public function store(Request $request)
    {
        $calendar = $this->resolveCalendarContext($request);
        $data = $this->validateEvento($request);
        $user = $request->user();
        $targetStrutturaId = $this->resolveManualEventTargetStrutturaId($request, $calendar);

        CalendarioEvento::withoutGlobalScopes()->create([
            'struttura_id' => $targetStrutturaId,
            'ambito' => $targetStrutturaId ? 'struttura' : 'personale',
            'user_scope_id' => $targetStrutturaId ? null : $user->id,
            'tipo' => 'manuale',
            'titolo' => $data['titolo'],
            'descrizione' => $data['descrizione'] ?? null,
            'data_evento' => $data['data_evento'],
            'ora_evento' => $data['ora_evento'] ?? null,
            'priorita' => $data['priorita'],
            'stato' => $data['stato'],
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'visto_at' => ($data['stato'] === 'vista') ? now() : null,
            'completato_at' => ($data['stato'] === 'completata') ? now() : null,
            'chiuso_at' => ($data['stato'] === 'chiusa') ? now() : null,
            'closed_by' => in_array($data['stato'], ['completata', 'chiusa'], true) ? $user->id : null,
        ]);

        return redirect()->route('calendario.index', [
            'contesto' => $calendar['contesto'],
            'struttura_id' => $targetStrutturaId ?: null,
            'mese' => Carbon::parse($data['data_evento'])->format('Y-m'),
            'giorno' => $data['data_evento'],
            'vista' => 'day',
        ])->with('success', 'Evento salvato nel calendario.');
    }

    public function update(Request $request, int $id)
    {
        $calendar = $this->resolveCalendarContext($request);
        $evento = CalendarioEvento::withoutGlobalScopes()->where('tipo', 'manuale')->findOrFail($id);
        $this->guardEventoAccess($request->user(), $evento);

        $data = $this->validateEvento($request);
        $evento->fill([
            'titolo' => $data['titolo'],
            'descrizione' => $data['descrizione'] ?? null,
            'data_evento' => $data['data_evento'],
            'ora_evento' => $data['ora_evento'] ?? null,
            'priorita' => $data['priorita'],
            'stato' => $data['stato'],
            'updated_by' => $request->user()->id,
        ]);

        $this->syncStateFields($evento, $request->user()->id);
        $evento->save();

        return redirect()->route('calendario.index', [
            'contesto' => $calendar['contesto'],
            'struttura_id' => $evento->struttura_id,
            'mese' => Carbon::parse($data['data_evento'])->format('Y-m'),
            'giorno' => $data['data_evento'],
            'vista' => 'day',
        ])->with('success', 'Evento aggiornato.');
    }

    public function updateStatus(Request $request, int $id)
    {
        $calendar = $this->resolveCalendarContext($request);
        $evento = CalendarioEvento::withoutGlobalScopes()->where('tipo', 'manuale')->findOrFail($id);
        $this->guardEventoAccess($request->user(), $evento);

        $data = $request->validate([
            'stato' => ['required', Rule::in(['da_fare', 'vista', 'completata', 'chiusa'])],
        ]);

        $evento->stato = $data['stato'];
        $evento->updated_by = $request->user()->id;
        $this->syncStateFields($evento, $request->user()->id);
        $evento->save();

        return redirect()->route('calendario.index', [
            'contesto' => $calendar['contesto'],
            'struttura_id' => $evento->struttura_id,
            'mese' => optional($evento->data_evento)->format('Y-m'),
            'giorno' => optional($evento->data_evento)->toDateString(),
            'vista' => 'day',
        ])->with('success', 'Stato evento aggiornato.');
    }

    private function validateEvento(Request $request): array
    {
        return $request->validate([
            'titolo' => ['required', 'string', 'max:180'],
            'descrizione' => ['nullable', 'string', 'max:5000'],
            'data_evento' => ['required', 'date'],
            'ora_evento' => ['nullable', 'date_format:H:i'],
            'priorita' => ['required', Rule::in(['bassa', 'normale', 'alta', 'urgente'])],
            'stato' => ['required', Rule::in(['da_fare', 'vista', 'completata', 'chiusa'])],
        ], [
            'titolo.required' => 'Inserisci il titolo dell\'evento.',
            'data_evento.required' => 'Seleziona la data dell\'evento.',
            'data_evento.date' => 'La data evento non e valida.',
            'ora_evento.date_format' => 'L\'ora deve essere nel formato HH:MM.',
        ]);
    }

    private function syncStateFields(CalendarioEvento $evento, int $userId): void
    {
        $evento->visto_at = in_array($evento->stato, ['vista', 'completata', 'chiusa'], true)
            ? ($evento->visto_at ?: now())
            : null;

        $evento->completato_at = in_array($evento->stato, ['completata', 'chiusa'], true)
            ? ($evento->completato_at ?: now())
            : null;

        if ($evento->stato === 'chiusa') {
            $evento->chiuso_at = $evento->chiuso_at ?: now();
            $evento->closed_by = $userId;
        } else {
            $evento->chiuso_at = null;
            $evento->closed_by = null;
        }
    }

    private function manualEventsQuery(User $user, string $contesto, ?int $strutturaId, array $allowedStructureIds = [])
    {
        return CalendarioEvento::withoutGlobalScopes()
            ->where('tipo', 'manuale')
            ->when($contesto === 'personale', fn ($query) => $query
                ->where('ambito', 'personale')
                ->where('user_scope_id', $user->id))
            ->when($contesto === 'struttura' && $strutturaId, fn ($query) => $query
                ->where('ambito', 'struttura')
                ->where('struttura_id', $strutturaId))
            ->when($contesto === 'portfolio', fn ($query) => $query
                ->where('ambito', 'struttura')
                ->whereIn('struttura_id', $allowedStructureIds));
    }

    private function resolveCalendarContext(Request $request): array
    {
        $user = $request->user();
        $struttureDisponibili = $this->accessibleStructures($user);
        $allowedStructureIds = $struttureDisponibili->pluck('id')->map(fn ($id) => (int) $id)->all();
        $requested = (string) $request->input('contesto', $request->query('contesto', $this->defaultCalendarContext($user, $struttureDisponibili)));
        $selectedStructureId = (int) ($request->input('struttura_id', $request->query('struttura_id')) ?: 0);

        $selectedStruttura = null;
        if (!empty($allowedStructureIds)) {
            if ($selectedStructureId && in_array($selectedStructureId, $allowedStructureIds, true)) {
                $selectedStruttura = $struttureDisponibili->firstWhere('id', $selectedStructureId);
            } elseif ($user->isStrutturaUser()) {
                $selectedStruttura = $struttureDisponibili->first();
            } elseif ($requested === 'struttura') {
                $selectedStruttura = $struttureDisponibili->first();
            }
        }

        $portfolioLabel = match (true) {
            $user->isSuperAdmin() => 'Calendario generale strutture',
            $user->isAdmin() => 'Calendario amministratore',
            $user->isProprietario() => 'Calendario proprietario',
            default => 'Calendario strutture',
        };

        if ($requested === 'struttura' && $selectedStruttura) {
            return [
                'contesto' => 'struttura',
                'struttura' => $selectedStruttura,
                'strutture_disponibili' => $struttureDisponibili,
                'allowed_structure_ids' => $allowedStructureIds,
                'can_select_structure' => !$user->isStrutturaUser() && $struttureDisponibili->isNotEmpty(),
                'portfolio_label' => $portfolioLabel,
            ];
        }

        if ($requested === 'portfolio' && $struttureDisponibili->isNotEmpty()) {
            return [
                'contesto' => 'portfolio',
                'struttura' => $selectedStruttura,
                'strutture_disponibili' => $struttureDisponibili,
                'allowed_structure_ids' => $allowedStructureIds,
                'can_select_structure' => !$user->isStrutturaUser() && $struttureDisponibili->isNotEmpty(),
                'portfolio_label' => $portfolioLabel,
            ];
        }

        return [
            'contesto' => 'personale',
            'struttura' => $selectedStruttura,
            'strutture_disponibili' => $struttureDisponibili,
            'allowed_structure_ids' => $allowedStructureIds,
            'can_select_structure' => !$user->isStrutturaUser() && $struttureDisponibili->isNotEmpty(),
            'portfolio_label' => $portfolioLabel,
        ];
    }

    private function accessibleStructures(User $user)
    {
        if ($user->struttura_id) {
            StrutturaCorrente::setId($user->struttura_id);
            return Struttura::query()->whereKey($user->struttura_id)->get();
        }

        if ($user->isSuperAdmin()) {
            return Struttura::query()->orderBy('nome_struttura')->get();
        }

        if ($user->isAdmin()) {
            return Struttura::query()
                ->whereHas('proprietario', fn ($query) => $query->where('admin_id', $user->id))
                ->orderBy('nome_struttura')
                ->get();
        }

        if ($user->isProprietario()) {
            return Struttura::query()
                ->where('proprietario_id', $user->proprietario_id)
                ->orderBy('nome_struttura')
                ->get();
        }

        $strutturaId = StrutturaCorrente::getId();
        return $strutturaId ? Struttura::query()->whereKey($strutturaId)->get() : collect();
    }

    private function defaultCalendarContext(User $user, $struttureDisponibili): string
    {
        if ($user->isStrutturaUser()) {
            return 'struttura';
        }

        if ($struttureDisponibili->isNotEmpty() && ($user->isSuperAdmin() || $user->isAdmin() || $user->isProprietario())) {
            return 'portfolio';
        }

        return 'personale';
    }

    private function resolveManualEventTargetStrutturaId(Request $request, array $calendar): ?int
    {
        if (($calendar['contesto'] ?? 'personale') === 'personale') {
            return null;
        }

        $selected = (int) ($request->input('struttura_id') ?: ($calendar['struttura']->id ?? 0));
        abort_unless($selected && in_array($selected, $calendar['allowed_structure_ids'] ?? [], true), 403);

        return $selected;
    }

    private function guardEventoAccess(User $user, CalendarioEvento $evento): void
    {
        if ($evento->ambito === 'personale') {
            abort_unless((int) $evento->user_scope_id === (int) $user->id, 403);
            return;
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->isAdmin()) {
            $allowedIds = Struttura::whereHas('proprietario', fn ($query) => $query->where('admin_id', $user->id))->pluck('id')->all();
            abort_unless(in_array((int) $evento->struttura_id, $allowedIds, true), 403);
            return;
        }

        if ($user->isProprietario()) {
            $allowedIds = Struttura::where('proprietario_id', $user->proprietario_id)->pluck('id')->all();
            abort_unless(in_array((int) $evento->struttura_id, $allowedIds, true), 403);
            return;
        }

        abort_unless((int) $evento->struttura_id === (int) $user->struttura_id, 403);
    }

    private function automaticEvents(Struttura $struttura, Carbon $start, Carbon $end): array
    {
        return array_merge(
            $this->birthdayEvents($struttura, $start, $end),
            $this->schedinaMovementEvents($struttura, $start, $end),
            $this->serviceDeadlineEvents($struttura, $start, $end),
            $this->licenseDeadlineEvents($struttura, $start, $end)
        );
    }

    private function serviceDeadlineEvents(Struttura $struttura, Carbon $start, Carbon $end): array
    {
        if (!$struttura->scadenza_servizio) {
            return [];
        }

        $scadenza = $struttura->scadenza_servizio->copy();
        if (!$scadenza->betweenIncluded($start, $end)) {
            return [];
        }

        $isPast = $scadenza->isPast();

        return [$this->automaticEventArray([
            'tipo' => 'scadenza_servizio',
            'titolo' => 'Scadenza servizio: ' . $struttura->nome_struttura,
            'descrizione' => 'Piano ' . ($struttura->piano ?: 'non definito') . ' - stato pagamento ' . ($struttura->stato_pagamento ?: 'non definito') . '.',
            'data_evento' => $scadenza->toDateString(),
            'ora_evento' => null,
            'priorita' => $isPast ? 'urgente' : 'alta',
            'stato' => $isPast ? 'chiusa' : 'da_fare',
            'creator_label' => 'Sistema',
            'detail_link' => $this->calendarStructureDetailLink($struttura->id, 'relazione'),
            'detail_label' => 'Apri relazione e pagamenti',
            'struttura_label' => $struttura->nome_struttura,
        ])];
    }

    private function licenseDeadlineEvents(Struttura $struttura, Carbon $start, Carbon $end): array
    {
        return LicenzaAssegnazione::query()
            ->with('articolo')
            ->where('struttura_id', $struttura->id)
            ->whereNotNull('data_scadenza')
            ->whereBetween('data_scadenza', [$start->toDateString(), $end->toDateString()])
            ->orderBy('data_scadenza')
            ->get()
            ->map(function (LicenzaAssegnazione $licenza) {
                $scadenza = $licenza->data_scadenza->copy();
                $isPast = $scadenza->isPast();

                return $this->automaticEventArray([
                    'tipo' => 'scadenza_licenza',
                    'titolo' => 'Scadenza licenza: ' . ($licenza->articolo?->nome ?: 'Licenza'),
                    'descrizione' => ($licenza->numero_licenza ?: 'Licenza senza numero') . ' - stato pagamento ' . ($licenza->stato_pagamento ?: 'non definito') . '.',
                    'data_evento' => $scadenza->toDateString(),
                    'ora_evento' => null,
                    'priorita' => $isPast ? 'urgente' : ($licenza->stato_pagamento === 'da_pagare' ? 'alta' : 'normale'),
                    'stato' => $isPast ? 'chiusa' : 'da_fare',
                    'creator_label' => 'Sistema',
                    'detail_link' => $this->calendarStructureDetailLink($struttura->id, 'licenze'),
                    'detail_label' => 'Apri licenze',
                    'struttura_label' => $struttura->nome_struttura,
                ]);
            })
            ->all();
    }

    private function birthdayEvents(Struttura $struttura, Carbon $start, Carbon $end): array
    {
        $events = [];
        $clienti = Customers::query()
            ->select(['id', 'name', 'surname', 'nac_reg'])
            ->where('struttura_id', $struttura->id)
            ->whereNotNull('nac_reg')
            ->where('nac_reg', '<>', '')
            ->get();

        foreach ($clienti as $cliente) {
            try {
                $birth = Carbon::parse($cliente->nac_reg);
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($this->yearsInRange($start, $end) as $year) {
                $eventDate = $birth->copy()->setYear($year);
                if ($eventDate->betweenIncluded($start, $end)) {
                    $events[] = $this->automaticEventArray([
                        'tipo' => 'compleanno',
                        'titolo' => 'Compleanno: ' . trim(($cliente->name ?? '') . ' ' . ($cliente->surname ?? '')),
                        'descrizione' => 'Il cliente compie ' . $birth->copy()->diffInYears($eventDate) . ' anni.',
                        'data_evento' => $eventDate->toDateString(),
                        'ora_evento' => null,
                        'priorita' => 'normale',
                        'stato' => $eventDate->isPast() ? 'chiusa' : 'da_fare',
                        'creator_label' => 'Sistema',
                        'struttura_label' => $struttura->nome_struttura,
                    ]);
                }
            }
        }

        $componenti = Componenti::query()
            ->select(['id', 'name', 'surname', 'date_nac'])
            ->where('struttura_id', $struttura->id)
            ->whereNotNull('date_nac')
            ->where('date_nac', '<>', '')
            ->get();

        foreach ($componenti as $componente) {
            try {
                $birth = Carbon::parse($componente->date_nac);
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($this->yearsInRange($start, $end) as $year) {
                $eventDate = $birth->copy()->setYear($year);
                if ($eventDate->betweenIncluded($start, $end)) {
                    $events[] = $this->automaticEventArray([
                        'tipo' => 'compleanno',
                        'titolo' => 'Compleanno componente: ' . trim(($componente->name ?? '') . ' ' . ($componente->surname ?? '')),
                        'descrizione' => 'Il componente compie ' . $birth->copy()->diffInYears($eventDate) . ' anni.',
                        'data_evento' => $eventDate->toDateString(),
                        'ora_evento' => null,
                        'priorita' => 'normale',
                        'stato' => $eventDate->isPast() ? 'chiusa' : 'da_fare',
                        'creator_label' => 'Sistema',
                        'struttura_label' => $struttura->nome_struttura,
                    ]);
                }
            }
        }

        return $events;
    }

    private function schedinaMovementEvents(Struttura $struttura, Carbon $start, Carbon $end): array
    {
        $events = [];
        $schedine = Schedina::query()
            ->select(['id', 'name', 'surname', 'arrive', 'departure', 'cant_people', 'circuito', 'is_arrive', 'struttura_id'])
            ->where('struttura_id', $struttura->id)
            ->where(function ($query) {
                $query->where('circuito', 'schedina')
                    ->orWhere('circuito', 'arrivi')
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('circuito')->where('is_arrive', 0);
                    });
            })
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('arrive', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('departure', [$start->toDateString(), $end->toDateString()]);
            })
            ->get();

        foreach ($schedine as $schedina) {
            $ospite = trim(($schedina->name ?? '') . ' ' . ($schedina->surname ?? '')) ?: 'Ospite';
            if ($schedina->arrive) {
                try {
                    $arrive = Carbon::parse($schedina->arrive);
                    if ($arrive->betweenIncluded($start, $end)) {
                        $events[] = $this->automaticEventArray([
                            'tipo' => 'checkin',
                            'titolo' => 'Check-in previsto: ' . $ospite,
                            'descrizione' => 'Arrivo previsto in struttura' . ($schedina->cant_people ? ' per ' . $schedina->cant_people . ' persone.' : '.'),
                            'data_evento' => $arrive->toDateString(),
                            'ora_evento' => null,
                            'priorita' => 'alta',
                            'stato' => $arrive->isPast() ? 'chiusa' : 'da_fare',
                            'creator_label' => 'Sistema',
                            'struttura_label' => $struttura->nome_struttura,
                        ]);
                    }
                } catch (\Throwable $e) {
                }
            }

            if ($schedina->departure) {
                try {
                    $departure = Carbon::parse($schedina->departure);
                    if ($departure->betweenIncluded($start, $end)) {
                        $events[] = $this->automaticEventArray([
                            'tipo' => 'checkout',
                            'titolo' => 'Check-out previsto: ' . $ospite,
                            'descrizione' => 'Partenza prevista dalla struttura' . ($schedina->cant_people ? ' per ' . $schedina->cant_people . ' persone.' : '.'),
                            'data_evento' => $departure->toDateString(),
                            'ora_evento' => null,
                            'priorita' => 'normale',
                            'stato' => $departure->isPast() ? 'chiusa' : 'da_fare',
                            'creator_label' => 'Sistema',
                            'struttura_label' => $struttura->nome_struttura,
                        ]);
                    }
                } catch (\Throwable $e) {
                }
            }
        }

        return $events;
    }

    private function presentManualEvent(CalendarioEvento $evento): array
    {
        $creator = $evento->creator ?: $evento->owner;

        return [
            'id' => $evento->id,
            'is_manual' => true,
            'tipo' => $evento->tipo,
            'tipo_label' => $evento->ambito === 'personale' ? 'Personale' : $evento->tipoLabel(),
            'titolo' => $evento->titolo,
            'descrizione' => $evento->descrizione,
            'data_evento' => $evento->data_evento?->toDateString(),
            'date_key' => $evento->data_evento?->toDateString(),
            'ora_evento' => $evento->ora_evento,
            'sort_time' => $evento->ora_evento ?: '99:99',
            'priorita' => $evento->priorita,
            'priorita_label' => $evento->prioritaLabel(),
            'stato' => $evento->stato,
            'stato_label' => $evento->statoLabel(),
            'badge_class' => $evento->ambito === 'personale' ? 'bg-primary-subtle text-primary' : $evento->badgeClass(),
            'creator_label' => $creator?->displayLabel() ?? 'Utente',
            'creator_role' => $creator?->ruoloOperativoLabel() ?? 'Utente',
            'struttura_label' => $evento->struttura?->nome_struttura,
            'model' => $evento,
        ];
    }

    private function automaticEventArray(array $payload): array
    {
        return [
            'id' => null,
            'is_manual' => false,
            'tipo' => $payload['tipo'],
            'tipo_label' => match ($payload['tipo']) {
                'compleanno' => 'Compleanno',
                'checkin' => 'Check-in',
                'checkout' => 'Check-out',
                'scadenza_servizio' => 'Scadenza servizio',
                'scadenza_licenza' => 'Scadenza licenza',
                default => 'Sistema',
            },
            'titolo' => $payload['titolo'],
            'descrizione' => $payload['descrizione'] ?? null,
            'data_evento' => $payload['data_evento'],
            'date_key' => $payload['data_evento'],
            'ora_evento' => $payload['ora_evento'],
            'sort_time' => $payload['ora_evento'] ?: '99:99',
            'priorita' => $payload['priorita'],
            'priorita_label' => ucfirst($payload['priorita']),
            'stato' => $payload['stato'],
            'stato_label' => match ($payload['stato']) {
                'chiusa' => 'Chiusa',
                'vista' => 'Vista',
                'completata' => 'Completata',
                default => 'Da fare',
            },
            'badge_class' => match ($payload['tipo']) {
                'compleanno' => 'bg-warning-subtle text-warning',
                'checkin' => 'bg-success-subtle text-success',
                'checkout' => 'bg-info-subtle text-info',
                'scadenza_servizio' => 'bg-danger-subtle text-danger',
                'scadenza_licenza' => 'bg-warning-subtle text-warning',
                default => 'bg-primary-subtle text-primary',
            },
            'creator_label' => $payload['creator_label'] ?? 'Sistema',
            'creator_role' => 'Automatico',
            'struttura_label' => $payload['struttura_label'] ?? null,
            'detail_link' => $payload['detail_link'] ?? null,
            'detail_label' => $payload['detail_label'] ?? null,
            'model' => null,
        ];
    }

    private function calendarStructureDetailLink(int $strutturaId, string $tab): string
    {
        $user = auth()->user();
        if (!$user) {
            return '#';
        }

        return match ($user->ruolo) {
            'super_admin' => route('superadmin.strutture.edit', ['id' => $strutturaId, 'tab' => $tab]),
            'admin' => route('admin.strutture.edit', ['id' => $strutturaId, 'tab' => $tab]),
            default => route('struttura.edit', ['tab' => $tab]),
        };
    }

    private function yearsInRange(Carbon $start, Carbon $end): array
    {
        $years = [];
        for ($year = $start->year; $year <= $end->year; $year++) {
            $years[] = $year;
        }

        return $years;
    }

    private function parseMonth(?string $value, ?string $day = null): Carbon
    {
        try {
            return $value
                ? Carbon::createFromFormat('Y-m', $value)->startOfMonth()
                : ($day ? Carbon::parse($day)->startOfMonth() : now()->startOfMonth());
        } catch (\Throwable $e) {
            return now()->startOfMonth();
        }
    }

    private function parseDay(?string $value, Carbon $month): Carbon
    {
        try {
            return $value
                ? Carbon::parse($value)->startOfDay()
                : now()->startOfDay();
        } catch (\Throwable $e) {
            return $month->copy()->startOfMonth();
        }
    }

    private function calendarSearchMatches(array $event, string $q): bool
    {
        if ($q === '') {
            return true;
        }

        $haystack = mb_strtolower(implode(' ', array_filter([
            $event['titolo'] ?? null,
            $event['descrizione'] ?? null,
            $event['tipo_label'] ?? null,
            $event['creator_label'] ?? null,
            $event['creator_role'] ?? null,
            $event['struttura_label'] ?? null,
            $event['stato_label'] ?? null,
        ])));

        return str_contains($haystack, mb_strtolower($q));
    }
}
