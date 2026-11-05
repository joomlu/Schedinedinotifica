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

        $request->validate([
            'vista' => ['nullable', Rule::in(['month', 'week', 'day', 'agenda'])],
            'categoria' => ['nullable', Rule::in(['tutte', 'informazioni', 'attivita', 'eventi'])],
            'giorno' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:1900-01-01', 'before_or_equal:2100-12-31'],
            'mese' => ['nullable', 'date_format:Y-m', 'after_or_equal:1900-01', 'before_or_equal:2100-12'],
            'q' => ['nullable', 'string', 'max:180'],
            'sezione' => ['nullable', Rule::in(['calendario', 'storico'])],
            'stato_storico' => ['nullable', Rule::in(['da_fare', 'vista', 'completata', 'chiusa'])],
        ]);
        $month = $this->parseMonth($request->query('mese'), $request->query('giorno'));
        $selectedDay = $request->filled('giorno') ? Carbon::parse($request->query('giorno'))->startOfDay()
            : ($month->isSameMonth(now()) ? now()->startOfDay() : $month->copy());
        $vista = (string) $request->query('vista', 'month');
        if ($vista !== 'month') {
            $month = $selectedDay->copy()->startOfMonth();
        }
        $categoria = (string) $request->query('categoria', 'tutte');
        $statoStorico = (string) $request->query('stato_storico', '');
        $q = trim((string) $request->query('q', ''));
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        $gridStart = match ($vista) {
            'day' => $selectedDay->copy(),
            'week' => $selectedDay->copy()->startOfWeek(Carbon::MONDAY),
            default => $monthStart->copy()->startOfWeek(Carbon::MONDAY),
        };
        $gridEnd = match ($vista) {
            'day' => $selectedDay->copy()->endOfDay(),
            'week' => $selectedDay->copy()->endOfWeek(Carbon::SUNDAY),
            default => $monthEnd->copy()->endOfWeek(Carbon::SUNDAY),
        };
        if ($vista === 'agenda') {
            $gridStart = $monthStart->copy();
            $gridEnd = $monthEnd->copy();
        }

        $manualMonthEvents = $this->manualEventsQuery($user, $contesto, $struttura?->id, $scopeStructureIds)
            ->when($categoria !== 'tutte', fn ($query) => $query->whereIn('tipo', match ($categoria) { 'informazioni' => ['nota'], 'eventi' => ['evento'], default => ['manuale', 'scadenza'] }))
            ->with(['creator', 'closer', 'owner', 'struttura'])
            ->whereBetween('data_evento', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->orderBy('data_evento')
            ->orderBy('ora_evento')
            ->orderByDesc('id')
            ->get();

        $automaticMonthEvents = collect();

        if ($contesto === 'struttura' && $struttura) {
            $automaticMonthEvents = collect($this->automaticEvents($struttura, $gridStart, $gridEnd));
        } elseif ($contesto === 'portfolio') {
            $automaticMonthEvents = $scopeStructures
                ->flatMap(fn (Struttura $item) => $this->automaticEvents($item, $gridStart, $gridEnd))
                ->values();
        }

        $allMonthEvents = $manualMonthEvents
            ->map(fn (CalendarioEvento $evento) => $this->presentManualEvent($evento))
            ->concat($automaticMonthEvents)
            ->filter(fn (array $event) => $this->calendarSearchMatches($event, $q))
            ->filter(fn (array $event) => $categoria === 'tutte' || $event['categoria'] === $categoria)
            ->sortBy([['data_evento', 'asc'], ['sort_time', 'asc'], ['titolo', 'asc']])
            ->values();

        $eventsByDay = $allMonthEvents->groupBy('date_key');

        $agendaEvents = $eventsByDay->get($selectedDay->toDateString(), collect());

        $history = collect();
        if ($request->query('sezione') === 'storico') {
            $history = $this->manualEventsQuery($user, $contesto, $struttura?->id, $scopeStructureIds)
                ->with(['creator', 'closer', 'owner', 'struttura'])
                ->where(function ($query) use ($selectedDay) {
                    $query->whereIn('stato', ['completata', 'chiusa'])
                        ->orWhereDate('data_evento', '<', $selectedDay->toDateString());
                })
                ->when($categoria !== 'tutte', fn ($query) => $query->whereIn('tipo', match ($categoria) { 'informazioni' => ['nota'], 'eventi' => ['evento'], default => ['manuale', 'scadenza'] }))
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
        }

        $calendarWeeks = collect(CarbonPeriod::create($gridStart, '1 day', $gridEnd))
            ->chunk(7)
            ->values();

        return view('calendario.index', [
            'struttura' => $struttura,
            'month' => $month,
            'selectedDay' => $selectedDay,
            'activeTab' => $vista,
            'vista' => $vista,
            'categoria' => $categoria,
            'periodStart' => $gridStart,
            'periodEnd' => $gridEnd,
            'periodEvents' => $allMonthEvents,
            'calendarWeeks' => $calendarWeeks,
            'eventsByDay' => $eventsByDay,
            'agendaEvents' => $agendaEvents,
            'history' => $history,
            'historyEvents' => $history->map(fn (CalendarioEvento $evento) => $this->presentManualEvent($evento)),
            'manualMonthEvents' => $manualMonthEvents,
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
            'tipo' => $data['tipo'] ?? 'manuale',
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
            'vista' => 'month',
        ])->with('calendar_day', $data['data_evento'])->with('success', 'Evento salvato nel calendario.');
    }

    public function update(Request $request, int $id)
    {
        $calendar = $this->resolveCalendarContext($request);
        $evento = CalendarioEvento::withoutGlobalScopes()->whereIn('tipo', ['manuale', 'nota', 'evento', 'scadenza'])->findOrFail($id);
        $this->guardEventoAccess($request->user(), $evento);

        $data = $this->validateEvento($request);
        $evento->fill([
            'tipo' => $data['tipo'] ?? $evento->tipo,
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
            'vista' => 'month',
        ])->with('calendar_day', $data['data_evento'])->with('success', 'Evento aggiornato.');
    }

    public function updateStatus(Request $request, int $id)
    {
        $calendar = $this->resolveCalendarContext($request);
        $evento = CalendarioEvento::withoutGlobalScopes()->whereIn('tipo', ['manuale', 'nota', 'evento', 'scadenza'])->findOrFail($id);
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
            'vista' => 'month',
        ])->with('calendar_day', $evento->data_evento->toDateString())->with('success', 'Stato evento aggiornato.');
    }

    private function validateEvento(Request $request): array
    {
        return $request->validate([
            'tipo' => ['sometimes', Rule::in(['manuale', 'nota', 'evento', 'scadenza'])],
            'titolo' => ['required', 'string', 'max:180'],
            'descrizione' => ['nullable', 'string', 'max:5000'],
            'data_evento' => ['required', 'date_format:Y-m-d', 'after_or_equal:1900-01-01', 'before_or_equal:2100-12-31'],
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
            ->whereIn('tipo', ['manuale', 'nota', 'evento', 'scadenza'])
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
        $request->validate([
            'contesto' => ['nullable', Rule::in(['personale', 'struttura', 'portfolio'])],
            'struttura_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $user = $request->user();
        $struttureDisponibili = $this->accessibleStructures($user);
        $allowedStructureIds = $struttureDisponibili->pluck('id')->map(fn ($id) => (int) $id)->all();
        $requested = (string) $request->input('contesto', $request->query('contesto', $this->defaultCalendarContext($user, $struttureDisponibili)));
        // Un solo hotel non costituisce un portafoglio: anche i vecchi URL
        // mantengono il contesto della struttura, senza opzioni ridondanti.
        if ($requested === 'portfolio' && $struttureDisponibili->count() === 1) {
            $requested = 'struttura';
        }
        $selectedStructureId = (int) ($request->input('struttura_id', $request->query('struttura_id')) ?: 0);

        abort_if($selectedStructureId && !in_array($selectedStructureId, $allowedStructureIds, true), 403);
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
                'can_select_structure' => !$user->isStrutturaUser() && $struttureDisponibili->count() > 1,
                'portfolio_label' => $portfolioLabel,
            ];
        }

        if ($requested === 'portfolio' && $struttureDisponibili->isNotEmpty()) {
            return [
                'contesto' => 'portfolio',
                'struttura' => $selectedStruttura,
                'strutture_disponibili' => $struttureDisponibili,
                'allowed_structure_ids' => $allowedStructureIds,
                'can_select_structure' => !$user->isStrutturaUser() && $struttureDisponibili->count() > 1,
                'portfolio_label' => $portfolioLabel,
            ];
        }

        return [
            'contesto' => 'personale',
            'struttura' => $selectedStruttura,
            'strutture_disponibili' => $struttureDisponibili,
            'allowed_structure_ids' => $allowedStructureIds,
            'can_select_structure' => !$user->isStrutturaUser() && $struttureDisponibili->count() > 1,
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
        // Il perimetro è già risolto da resolveCalendarContext: il filtro
        // esplicito evita che l’hotel selezionato nella barra nasconda gli altri autorizzati.
        $clienti = Customers::withoutGlobalScope('struttura')
            ->select(['id', 'name', 'surname', 'nac_reg'])
            ->where('struttura_id', $struttura->id)
            ->whereNotNull('nac_reg')
            ->get();

        foreach ($clienti as $cliente) {
            try {
                $birth = Carbon::parse($cliente->nac_reg);
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($this->yearsInRange($start, $end) as $year) {
                $eventDate = $birth->copy()->startOfMonth()->setYear($year)->day(min($birth->day, $birth->copy()->startOfMonth()->setYear($year)->daysInMonth));
                if ($eventDate->gte($birth) && $eventDate->betweenIncluded($start, $end)) {
                    $events[] = $this->automaticEventArray([
                        'tipo' => 'compleanno',
                        'titolo' => 'Compleanno: ' . trim(($cliente->name ?? '') . ' ' . ($cliente->surname ?? '')),
                        'detail_link' => route('customer.edit', ['id' => $cliente->id]),
                        'detail_label' => 'Apri cliente',
                        'descrizione' => 'Data di nascita: ' . $birth->format('d/m/Y') . '. Compleanno del cliente: ' . ($year - $birth->year) . ' anni.',
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

        $componenti = Componenti::withoutGlobalScope('struttura')
            ->select(['id', 'name', 'surname', 'date_nac'])
            ->where('struttura_id', $struttura->id)
            ->whereNotNull('date_nac')
            ->get();

        foreach ($componenti as $componente) {
            try {
                $birth = Carbon::parse($componente->date_nac);
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($this->yearsInRange($start, $end) as $year) {
                $eventDate = $birth->copy()->startOfMonth()->setYear($year)->day(min($birth->day, $birth->copy()->startOfMonth()->setYear($year)->daysInMonth));
                if ($eventDate->gte($birth) && $eventDate->betweenIncluded($start, $end)) {
                    $events[] = $this->automaticEventArray([
                        'tipo' => 'compleanno',
                        'titolo' => 'Compleanno componente: ' . trim(($componente->name ?? '') . ' ' . ($componente->surname ?? '')),
                        'descrizione' => 'Data di nascita: ' . $birth->format('d/m/Y') . '. Compleanno del componente: ' . ($year - $birth->year) . ' anni.',
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
        $schedine = Schedina::withoutGlobalScope('struttura')->effettive()
            ->with(['componenti' => fn ($query) => $query->withoutGlobalScope('struttura')->where('struttura_id', $struttura->id)->select(['id', 'schedina_id', 'name', 'surname'])])
            ->select(['id', 'name', 'surname', 'arrive', 'departure', 'cant_people', 'circuito', 'is_arrive', 'struttura_id'])
            ->where('struttura_id', $struttura->id)
            ->where(function ($query) {
                $query->where('circuito', 'schedina')
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
            $componenti = $schedina->componenti->map(fn ($item) => trim($item->name . ' ' . $item->surname))->implode(', ');
            $dettaglio = 'Cliente principale: ' . $ospite . '. Ospiti dichiarati: ' . ($schedina->cant_people ?? 'non indicati')
                . '. Componenti registrati: ' . ($componenti ?: 'nessuno') . '.';
            if ($schedina->arrive) {
                try {
                    $arrive = Carbon::parse($schedina->arrive);
                    if ($arrive->betweenIncluded($start, $end)) {
                        $events[] = $this->automaticEventArray([
                            'tipo' => 'checkin',
                            'titolo' => 'Arrivo: ' . $ospite,
                            'descrizione' => $dettaglio,
                            'detail_link' => route('schedina.edit', ['id' => $schedina->id]),
                            'detail_label' => 'Apri soggiorno',
                            'persone' => $schedina->cant_people,
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
                            'titolo' => 'Partenza: ' . $ospite,
                            'descrizione' => $dettaglio,
                            'detail_link' => route('schedina.edit', ['id' => $schedina->id]),
                            'detail_label' => 'Apri soggiorno',
                            'persone' => $schedina->cant_people,
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
            'tipo_label' => $evento->tipoLabel(),
            'categoria' => match ($evento->tipo) { 'nota' => 'informazioni', 'evento' => 'eventi', default => 'attivita' },
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
            'badge_class' => $evento->badgeClass(),
            ...$this->alertPresentation($evento->priorita, $evento->data_evento?->toDateString(), $evento->stato, $evento->tipo === 'scadenza'),
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
            'categoria' => $payload['tipo'] === 'compleanno' ? 'eventi' : 'informazioni',
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
            ...$this->alertPresentation($payload['priorita'], $payload['data_evento'], 'informativo', in_array($payload['tipo'], ['scadenza_servizio', 'scadenza_licenza'], true)),
            'stato' => 'informativo',
            'stato_label' => match ($payload['tipo']) {
                'checkin', 'checkout' => 'Registrato in schedina',
                'compleanno' => 'Ricorrenza',
                default => 'Scadenza',
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
            'persone' => $payload['persone'] ?? null,
            'detail_link' => $payload['detail_link'] ?? null,
            'detail_label' => $payload['detail_label'] ?? null,
            'model' => null,
        ];
    }

    private function alertPresentation(string $priority, ?string $date, string $state, bool $deadline): array
    {
        $closed = in_array($state, ['completata', 'chiusa'], true);
        $color = $closed ? 'success' : match ($priority) {
            'urgente' => 'danger', 'alta' => 'warning', default => 'success',
        };
        $label = $closed ? ucfirst($state) : match ($priority) {
            'urgente' => 'Urgente', 'alta' => 'Attenzione', 'bassa' => 'Bassa priorità', default => 'Ordinario',
        };
        if ($deadline && !$closed && $date) {
            $day = Carbon::parse($date)->startOfDay();
            if ($day->lt(today())) {
                $color = 'danger';
                $label = 'Scaduta';
            } elseif ($day->isToday()) {
                $color = $priority === 'urgente' ? 'danger' : 'warning';
                $label = 'Scade oggi';
            }
        }

        return ['alert_color' => $color, 'alert_label' => $label, 'is_deadline' => $deadline];
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
                ? Carbon::createFromFormat('!Y-m', $value)->startOfMonth()
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
