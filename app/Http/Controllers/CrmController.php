<?php

namespace App\Http\Controllers;

use App\Models\CrmLead;
use App\Models\CrmLeadActivity;
use App\Models\GeoComune;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CrmController extends Controller
{
    private const MODALITA_CONTATTO = [
        'Richiesta demo' => 'Richiesta demo',
        'Informazioni generali' => 'Informazioni generali',
        'Richiesta commerciale' => 'Richiesta commerciale',
        'Attivazione e onboarding' => 'Attivazione e onboarding',
        'Supporto operativo' => 'Supporto operativo',
        'Partnership o collaborazione' => 'Partnership o collaborazione',
        'Contatto telefonico diretto' => 'Contatto telefonico diretto',
        'Contatto da fiera o evento' => 'Contatto da fiera o evento',
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $isSuperAdmin = $user->isSuperAdmin();
        abort_unless($isSuperAdmin || $user->isAdmin(), 403);

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'stato' => trim((string) $request->query('stato', '')),
            'admin_id' => (int) $request->query('admin_id', 0),
        ];
        $selectedDay = $request->query('giorno')
            ? Carbon::parse((string) $request->query('giorno'))->startOfDay()
            : now()->startOfDay();
        $selectedMonth = $request->query('mese_ref')
            ? Carbon::parse((string) $request->query('mese_ref'))->startOfMonth()
            : $selectedDay->copy()->startOfMonth();

        $query = CrmLead::query()->with(['assignedAdmin', 'activities']);
        $this->applyVisibility($query, $user);

        if (array_key_exists($filters['stato'], CrmLead::STATI)) {
            $query->where('stato', $filters['stato']);
        }

        if ($isSuperAdmin && $filters['admin_id'] > 0) {
            $query->where('assigned_admin_id', $filters['admin_id']);
        }

        if ($filters['q'] !== '') {
            $like = '%' . $filters['q'] . '%';
            $query->where(function ($inner) use ($like) {
                $inner->where('lead_code', 'like', $like)
                    ->orWhere('struttura', 'like', $like)
                    ->orWhere('nome_cognome', 'like', $like)
                    ->orWhere('persona_contatto', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('localita', 'like', $like)
                    ->orWhere('messaggio', 'like', $like);
            });
        }

        $leads = $query
            ->orderByRaw("case when stato = 'nuovo' then 0 when stato = 'da_contattare' then 1 when stato = 'in_contatto' then 2 when stato = 'demo_fissata' then 3 when stato = 'proposta_inviata' then 4 when stato = 'in_attesa_cliente' then 5 else 6 end")
            ->orderByRaw('case when prossimo_contatto_at is null then 1 else 0 end')
            ->orderBy('prossimo_contatto_at')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $agenda = CrmLeadActivity::query()
            ->with(['lead.assignedAdmin', 'user'])
            ->whereNotNull('scheduled_at')
            ->whereIn('stato', ['da_fare', 'registrata'])
            ->whereHas('lead', function ($inner) use ($user) {
                $this->applyVisibility($inner, $user);
            })
            ->orderBy('scheduled_at')
            ->limit(12)
            ->get();

        $agendaByDay = CrmLeadActivity::query()
            ->with(['lead.assignedAdmin', 'user'])
            ->whereNotNull('scheduled_at')
            ->whereDate('scheduled_at', $selectedDay->toDateString())
            ->whereHas('lead', function ($inner) use ($user) {
                $this->applyVisibility($inner, $user);
            })
            ->orderBy('scheduled_at')
            ->get();

        $agendaByMonth = CrmLeadActivity::query()
            ->with(['lead.assignedAdmin', 'user'])
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$selectedMonth->copy()->startOfMonth(), $selectedMonth->copy()->endOfMonth()])
            ->whereHas('lead', function ($inner) use ($user) {
                $this->applyVisibility($inner, $user);
            })
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn (CrmLeadActivity $activity) => $activity->scheduled_at?->format('Y-m-d'));

        $contatoriBase = CrmLead::query();
        $this->applyVisibility($contatoriBase, $user);
        $allVisible = $contatoriBase->get();

        $admins = $isSuperAdmin
            ? User::query()->whereIn('ruolo', ['admin', 'admin_disabled'])->orderBy('name')->get()
            : collect([$user]);

        $leadOptionsQuery = CrmLead::query();
        $this->applyVisibility($leadOptionsQuery, $user);
        $leadOptions = $leadOptionsQuery
            ->orderBy('struttura')
            ->orderBy('nome_cognome')
            ->get(['id', 'struttura', 'nome_cognome']);

        $localitaOptions = GeoComune::query()
            ->orderBy('nome')
            ->pluck('nome')
            ->filter()
            ->unique()
            ->values();

        return view('crm.index', [
            'routePrefix' => $isSuperAdmin ? 'superadmin.crm' : 'admin.crm',
            'isSuperAdmin' => $isSuperAdmin,
            'filters' => $filters,
            'leads' => $leads,
            'agenda' => $agenda,
            'admins' => $admins,
            'stati' => CrmLead::STATI,
            'selectedDay' => $selectedDay,
            'selectedMonth' => $selectedMonth,
            'agendaByDay' => $agendaByDay,
            'agendaByMonth' => $agendaByMonth,
            'leadOptions' => $leadOptions,
            'localitaOptions' => $localitaOptions,
            'modalitaOptions' => self::MODALITA_CONTATTO,
            'contatori' => [
                'nuovi' => $allVisible->where('stato', 'nuovo')->count(),
                'da_contattare' => $allVisible->where('stato', 'da_contattare')->count(),
                'demo' => $allVisible->where('stato', 'demo_fissata')->count(),
                'chiusi' => $allVisible->whereIn('stato', ['chiuso_vinto', 'chiuso_perso'])->count(),
                'agenda' => $agenda->count(),
            ],
        ]);
    }

    public function storeLead(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isSuperAdmin() || $user->isAdmin(), 403);

        $adminRule = $user->isSuperAdmin()
            ? ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('ruolo', ['admin', 'admin_disabled']))]
            : ['nullable', Rule::in([$user->id])];

        $data = $request->validate([
            'struttura' => ['required', 'string', 'max:120'],
            'nome_cognome' => ['required', 'string', 'max:120'],
            'persona_contatto' => ['nullable', 'string', 'max:120'],
            'localita' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'cellulare' => ['nullable', 'string', 'max:40'],
            'sito_web' => ['nullable', 'string', 'max:180'],
            'modalita_contatto' => ['nullable', Rule::in(array_keys(self::MODALITA_CONTATTO))],
            'messaggio' => ['nullable', 'string', 'max:5000'],
            'assigned_admin_id' => $adminRule,
        ]);

        $lead = CrmLead::create([
            'lead_code' => $this->nextLeadCode(),
            'fonte' => 'manuale_admin',
            'assigned_admin_id' => $data['assigned_admin_id'] ?? ($user->isAdmin() ? $user->id : null),
            'created_by_user_id' => $user->id,
            'stato' => 'nuovo',
            'struttura' => trim((string) $data['struttura']),
            'nome_cognome' => trim((string) $data['nome_cognome']),
            'persona_contatto' => filled($data['persona_contatto'] ?? null) ? trim((string) $data['persona_contatto']) : null,
            'localita' => filled($data['localita'] ?? null) ? trim((string) $data['localita']) : null,
            'email' => trim((string) $data['email']),
            'telefono' => filled($data['telefono'] ?? null) ? trim((string) $data['telefono']) : null,
            'cellulare' => filled($data['cellulare'] ?? null) ? trim((string) $data['cellulare']) : null,
            'sito_web' => filled($data['sito_web'] ?? null) ? trim((string) $data['sito_web']) : null,
            'modalita_contatto' => filled($data['modalita_contatto'] ?? null) ? trim((string) $data['modalita_contatto']) : null,
            'messaggio' => filled($data['messaggio'] ?? null) ? trim((string) $data['messaggio']) : null,
        ]);

        CrmLeadActivity::create([
            'crm_lead_id' => $lead->id,
            'user_id' => $user->id,
            'tipo' => 'nota',
            'direzione' => 'interna',
            'titolo' => 'Contatto CRM creato manualmente',
            'descrizione' => $lead->messaggio ?: 'Scheda CRM creata da area amministrativa.',
            'stato' => 'registrata',
        ]);

        return redirect()->route($user->isSuperAdmin() ? 'superadmin.crm.show' : 'admin.crm.show', ['id' => $lead->id])
            ->with('success', 'Nuovo contatto CRM creato.');
    }

    public function createExampleLead(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isSuperAdmin() || $user->isAdmin(), 403);

        $lead = CrmLead::create([
            'lead_code' => $this->nextLeadCode(),
            'fonte' => 'manuale_admin',
            'assigned_admin_id' => $user->isAdmin() ? $user->id : null,
            'created_by_user_id' => $user->id,
            'stato' => 'da_contattare',
            'struttura' => 'Hotel Demo Riviera',
            'nome_cognome' => 'Mario Rossi',
            'persona_contatto' => 'Direzione struttura',
            'localita' => 'Bellaria-Igea Marina',
            'email' => 'demo.hotel@example.com',
            'telefono' => '0541 123456',
            'cellulare' => '+39 393991968',
            'sito_web' => 'hoteldemoriviera.it',
            'modalita_contatto' => 'Richiesta demo',
            'messaggio' => 'Buongiorno, vorremmo vedere una demo completa del programma con focus su clienti, schedine, tassa di soggiorno e invio telematico.',
            'prossimo_contatto_at' => now()->addDay()->setTime(11, 0),
        ]);

        CrmLeadActivity::insert([
            [
                'crm_lead_id' => $lead->id,
                'user_id' => $user->id,
                'tipo' => 'richiesta_web',
                'direzione' => 'entrata',
                'titolo' => 'Richiesta demo dal sito',
                'descrizione' => 'Buongiorno, vorremmo vedere una demo completa del programma con focus su clienti, schedine, tassa di soggiorno e invio telematico.',
                'stato' => 'registrata',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'crm_lead_id' => $lead->id,
                'user_id' => $user->id,
                'tipo' => 'telefono',
                'direzione' => 'uscita',
                'titolo' => 'Primo richiamo commerciale',
                'descrizione' => 'Contatto telefonico iniziale per confermare l interesse e proporre una demo guidata.',
                'stato' => 'registrata',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'crm_lead_id' => $lead->id,
                'user_id' => $user->id,
                'tipo' => 'demo',
                'direzione' => 'interna',
                'titolo' => 'Demo completa con la struttura',
                'descrizione' => 'Agenda esempio: presentazione piattaforma, demo clienti e schedine, parte tassa e invio telematico, raccolta domande finali.',
                'scheduled_at' => now()->addDay()->setTime(11, 0),
                'stato' => 'da_fare',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return redirect()->route($user->isSuperAdmin() ? 'superadmin.crm.show' : 'admin.crm.show', ['id' => $lead->id, 'tab' => 'agenda'])
            ->with('success', 'Scheda CRM di esempio creata.');
    }

    public function show(Request $request, int $id): View
    {
        $lead = CrmLead::query()
            ->with(['assignedAdmin', 'createdBy', 'activities.user'])
            ->findOrFail($id);

        $this->guardLeadAccess($request->user(), $lead);

        $user = $request->user();
        $isSuperAdmin = $user->isSuperAdmin();

        $admins = $isSuperAdmin
            ? User::query()->whereIn('ruolo', ['admin', 'admin_disabled'])->orderBy('name')->get()
            : collect([$user]);

        return view('crm.show', [
            'lead' => $lead,
            'routePrefix' => $isSuperAdmin ? 'superadmin.crm' : 'admin.crm',
            'isSuperAdmin' => $isSuperAdmin,
            'admins' => $admins,
            'stati' => CrmLead::STATI,
            'tipiAttivita' => CrmLeadActivity::TIPI,
            'direzioniAttivita' => CrmLeadActivity::DIREZIONI,
            'statiAttivita' => CrmLeadActivity::STATI,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $lead = CrmLead::query()->findOrFail($id);
        $user = $request->user();
        $this->guardLeadAccess($user, $lead);

        $adminRule = $user->isSuperAdmin()
            ? ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('ruolo', ['admin', 'admin_disabled']))]
            : ['nullable', Rule::in([$user->id])];

        $data = $request->validate([
            'stato' => ['required', Rule::in(array_keys(CrmLead::STATI))],
            'assigned_admin_id' => $adminRule,
            'prossimo_contatto_data' => ['nullable', 'date'],
            'prossimo_contatto_ora' => ['nullable', 'date_format:H:i'],
            'note_interne' => ['nullable', 'string', 'max:10000'],
        ]);

        $prossimoContattoAt = $this->mergeDateAndTime(
            $data['prossimo_contatto_data'] ?? null,
            $data['prossimo_contatto_ora'] ?? null
        );

        $lead->fill([
            'stato' => $data['stato'],
            'assigned_admin_id' => $data['assigned_admin_id'] ?? ($user->isAdmin() ? $user->id : null),
            'prossimo_contatto_at' => $prossimoContattoAt,
            'note_interne' => $data['note_interne'] ?? null,
            'chiuso_at' => in_array($data['stato'], ['chiuso_vinto', 'chiuso_perso'], true) ? now() : null,
        ]);
        $lead->save();

        return back()->with('success', 'Scheda CRM aggiornata.');
    }

    public function storeActivity(Request $request, int $id): RedirectResponse
    {
        $lead = CrmLead::query()->findOrFail($id);
        $user = $request->user();
        $this->guardLeadAccess($user, $lead);

        $data = $request->validate([
            'tipo' => ['required', Rule::in(array_keys(CrmLeadActivity::TIPI))],
            'direzione' => ['required', Rule::in(array_keys(CrmLeadActivity::DIREZIONI))],
            'titolo' => ['required', 'string', 'max:180'],
            'descrizione' => ['nullable', 'string', 'max:10000'],
            'scheduled_data' => ['nullable', 'date'],
            'scheduled_ora' => ['nullable', 'date_format:H:i'],
            'stato' => ['required', Rule::in(array_keys(CrmLeadActivity::STATI))],
        ]);

        $scheduledAt = $this->mergeDateAndTime(
            $data['scheduled_data'] ?? null,
            $data['scheduled_ora'] ?? null
        );

        DB::transaction(function () use ($lead, $user, $data, $scheduledAt) {
            CrmLeadActivity::create([
                'crm_lead_id' => $lead->id,
                'user_id' => $user->id,
                'tipo' => $data['tipo'],
                'direzione' => $data['direzione'],
                'titolo' => $data['titolo'],
                'descrizione' => $data['descrizione'] ?? null,
                'scheduled_at' => $scheduledAt,
                'stato' => $data['stato'],
                'completed_at' => $data['stato'] === 'completata' ? now() : null,
            ]);

            $lead->forceFill([
                'ultimo_contatto_at' => now(),
                'prossimo_contatto_at' => $scheduledAt ?? $lead->prossimo_contatto_at,
                'assigned_admin_id' => $lead->assigned_admin_id ?: ($user->isAdmin() ? $user->id : $lead->assigned_admin_id),
            ])->save();
        });

        return back()->with('success', 'Attività CRM salvata.');
    }

    public function storeIndexAgenda(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isSuperAdmin() || $user->isAdmin(), 403);

        $data = $request->validate([
            'lead_id' => ['required', 'integer', 'exists:crm_leads,id'],
            'tipo' => ['required', Rule::in(array_keys(CrmLeadActivity::TIPI))],
            'titolo' => ['required', 'string', 'max:180'],
            'descrizione' => ['nullable', 'string', 'max:10000'],
            'scheduled_data' => ['required', 'date'],
            'scheduled_ora' => ['nullable', 'date_format:H:i'],
        ]);

        $lead = CrmLead::query()->findOrFail((int) $data['lead_id']);
        $this->guardLeadAccess($user, $lead);

        $scheduledAt = $this->mergeDateAndTime(
            $data['scheduled_data'] ?? null,
            $data['scheduled_ora'] ?? null
        );

        CrmLeadActivity::create([
            'crm_lead_id' => $lead->id,
            'user_id' => $user->id,
            'tipo' => $data['tipo'],
            'direzione' => 'interna',
            'titolo' => $data['titolo'],
            'descrizione' => $data['descrizione'] ?? null,
            'scheduled_at' => $scheduledAt,
            'stato' => 'da_fare',
        ]);

        $lead->forceFill([
            'ultimo_contatto_at' => now(),
            'prossimo_contatto_at' => $scheduledAt,
            'assigned_admin_id' => $lead->assigned_admin_id ?: ($user->isAdmin() ? $user->id : $lead->assigned_admin_id),
        ])->save();

        $route = $user->isSuperAdmin() ? 'superadmin.crm.index' : 'admin.crm.index';

        return redirect()->route($route, [
            'tab' => 'agenda',
            'giorno' => $scheduledAt?->format('Y-m-d'),
            'mese_ref' => $scheduledAt?->copy()->startOfMonth()->format('Y-m-d'),
        ])->with('success', 'Appuntamento CRM salvato in agenda.');
    }

    public function addExampleAgenda(Request $request, int $id): RedirectResponse
    {
        $lead = CrmLead::query()->findOrFail($id);
        $user = $request->user();
        $this->guardLeadAccess($user, $lead);

        CrmLeadActivity::create([
            'crm_lead_id' => $lead->id,
            'user_id' => $user->id,
            'tipo' => 'demo',
            'direzione' => 'interna',
            'titolo' => 'Demo conoscitiva con la struttura',
            'descrizione' => 'Esempio agenda CRM: presentazione generale del software, verifica esigenze della struttura, raccolta domande e definizione del prossimo passo commerciale.',
            'scheduled_at' => now()->addDays(2)->setTime(10, 30),
            'stato' => 'da_fare',
        ]);

        $lead->forceFill([
            'prossimo_contatto_at' => now()->addDays(2)->setTime(10, 30),
            'assigned_admin_id' => $lead->assigned_admin_id ?: ($user->isAdmin() ? $user->id : $lead->assigned_admin_id),
        ])->save();

        return redirect()
            ->route($user->isSuperAdmin() ? 'superadmin.crm.show' : 'admin.crm.show', ['id' => $lead->id, 'tab' => 'agenda'])
            ->with('success', 'Esempio agenda CRM creato.');
    }

    public function updateActivityStatus(Request $request, int $id, int $activityId): RedirectResponse
    {
        $lead = CrmLead::query()->findOrFail($id);
        $this->guardLeadAccess($request->user(), $lead);

        $activity = CrmLeadActivity::query()
            ->where('crm_lead_id', $lead->id)
            ->findOrFail($activityId);

        $data = $request->validate([
            'stato' => ['required', Rule::in(array_keys(CrmLeadActivity::STATI))],
        ]);

        $activity->stato = $data['stato'];
        $activity->completed_at = $data['stato'] === 'completata' ? now() : null;
        $activity->save();

        return back()->with('success', 'Stato attività aggiornato.');
    }

    private function applyVisibility($query, User $user): void
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        abort_unless($user->isAdmin(), 403);

        $query->where(function ($inner) use ($user) {
            $inner->whereNull('assigned_admin_id')
                ->orWhere('assigned_admin_id', $user->id);
        });
    }

    private function guardLeadAccess(User $user, CrmLead $lead): void
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        abort_unless($user->isAdmin(), 403);
        abort_unless($lead->assigned_admin_id === null || (int) $lead->assigned_admin_id === (int) $user->id, 403);
    }

    private function nextLeadCode(): string
    {
        $last = CrmLead::withoutGlobalScopes()
            ->where('lead_code', 'like', 'CRM-%')
            ->orderByDesc('id')
            ->value('lead_code');

        $next = 1;

        if (is_string($last) && preg_match('/CRM-(\d+)/', $last, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return sprintf('CRM-%06d', $next);
    }

    private function mergeDateAndTime(?string $date, ?string $time): ?Carbon
    {
        if (!$date) {
            return null;
        }

        $timeValue = $time ?: '09:00';

        return Carbon::parse($date . ' ' . $timeValue);
    }
}
