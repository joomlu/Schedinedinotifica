<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use App\Models\LicenzaAssegnazione;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WebCheckinRichiesta;
use App\Services\NotificheService;
use App\Support\StrutturaCorrente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return abort(404);
    }

    public function root()
    {
        $user = Auth::user();
        $strutturaId = StrutturaCorrente::getId() ?? $user?->struttura_id;
        $strutturaDashboard = $strutturaId
            ? Struttura::query()->with(['proprietario.admin'])->find($strutturaId)
            : null;

        $dashboardData = null;

        if ($strutturaDashboard) {
            $licenze = LicenzaAssegnazione::query()
                ->with('articolo')
                ->where('struttura_id', $strutturaDashboard->id)
                ->orderByDesc('attiva')
                ->orderBy('data_scadenza')
                ->get();

            $licenzeAttive = $licenze->where('attiva', true);
            $licenzeDaPagare = $licenze->whereIn('stato_pagamento', ['da_pagare', 'sospeso']);
            $prossimaScadenza = $licenze
                ->filter(fn ($licenza) => filled($licenza->data_scadenza))
                ->sortBy('data_scadenza')
                ->first()?->data_scadenza ?: $strutturaDashboard->scadenza_servizio;
            $prodottoPrincipale = $licenzeAttive->first()?->articolo?->nome
                ?: ($strutturaDashboard->piano ? 'Schedine di Notifica ' . strtoupper((string) $strutturaDashboard->piano) : null);
            $notificheTopbar = app(NotificheService::class)->topbarForUser($user);

            $dashboardData = [
                'struttura' => $strutturaDashboard,
                'owner' => $strutturaDashboard->proprietario,
                'ownerAdmin' => $strutturaDashboard->proprietario?->admin,
                'licenze' => $licenze,
                'licenze_attive' => $licenzeAttive->count(),
                'licenze_da_pagare' => $licenzeDaPagare->count(),
                'licenza_principale' => $licenzeAttive->first() ?: $licenze->first(),
                'prodotto_principale' => $prodottoPrincipale ?: 'Nessuna licenza attiva',
                'prossima_scadenza' => $prossimaScadenza,
                'totale_licenze' => (float) $licenze->sum('prezzo'),
                'notifiche_non_lette' => (int) ($notificheTopbar['non_lette'] ?? 0),
                'supporto_aperto' => SupportTicket::query()
                    ->where('struttura_id', $strutturaDashboard->id)
                    ->whereIn('stato', ['aperto', 'in_lavorazione', 'in_attesa_struttura'])
                    ->count(),
                'clienti' => Customers::query()->where('struttura_id', $strutturaDashboard->id)->count(),
                'schedine' => Schedina::query()->withoutGlobalScopes()->where('struttura_id', $strutturaDashboard->id)->count(),
                'arrivi' => Schedina::query()->withoutGlobalScopes()->where('struttura_id', $strutturaDashboard->id)->where('is_arrive', 1)->count(),
                'web_checkin' => WebCheckinRichiesta::query()->where('struttura_id', $strutturaDashboard->id)->whereIn('stato', ['nuova', 'inviata', 'aperta', 'compilata'])->count(),
                'sections' => [
                    [
                        'title' => 'Dati struttura',
                        'icon' => 'ri-building-line',
                        'route' => route('struttura.edit'),
                        'description' => 'Identità, contatti, GEO, dati fiscali e credenziali della struttura.',
                    ],
                    [
                        'title' => 'Configurazioni',
                        'icon' => 'ri-settings-3-line',
                        'route' => route('tassa_di_soggiorno.edit'),
                        'description' => 'Tabelle, tassa di soggiorno e configurazioni di lavoro della struttura.',
                        'links' => [
                            ['label' => 'Tassa di soggiorno', 'route' => route('tassa_di_soggiorno.edit')],
                            ['label' => 'Gruppi', 'route' => route('gruppi')],
                            ['label' => 'Tipo documenti', 'route' => route('tipo_documento.index')],
                        ],
                    ],
                    [
                        'title' => 'Clienti',
                        'icon' => 'ri-team-line',
                        'route' => route('customers'),
                        'description' => 'Anagrafiche clienti, storico e nuove registrazioni.',
                        'badge' => Customers::query()->where('struttura_id', $strutturaDashboard->id)->count() . ' clienti',
                    ],
                    [
                        'title' => 'Schedine',
                        'icon' => 'ri-file-list-3-line',
                        'route' => route('schedina'),
                        'description' => 'Schedine ufficiali, bozze e arrivi in lavorazione.',
                        'links' => [
                            ['label' => 'Liste', 'route' => route('schedina')],
                            ['label' => 'Bozze', 'route' => route('schedina.bozze')],
                            ['label' => 'Arrivi', 'route' => route('arrivals')],
                        ],
                    ],
                    [
                        'title' => 'Web Check-in',
                        'icon' => 'ri-smartphone-line',
                        'route' => route('schedina.web'),
                        'description' => 'Richieste online e pratiche che devono ancora entrare nel circuito operativo.',
                    ],
                    [
                        'title' => 'Invio telematico',
                        'icon' => 'ri-send-plane-line',
                        'route' => route('questura.index'),
                        'description' => 'Questura, Tavola A Emilia-Romagna e rapporto tassa di soggiorno.',
                        'links' => [
                            ['label' => 'Questura', 'route' => route('questura.index')],
                            ['label' => 'Tavola A', 'route' => route('istat.tabella_a.index')],
                            ['label' => 'Tassa', 'route' => route('tassa_di_soggiorno.rapporto')],
                        ],
                    ],
                    [
                        'title' => 'Statistica',
                        'icon' => 'ri-bar-chart-box-line',
                        'route' => route('presenze.index'),
                        'description' => 'Presenze, movimento e lettura statistica della struttura.',
                    ],
                    [
                        'title' => 'Calendario',
                        'icon' => 'ri-calendar-event-line',
                        'route' => route('calendario.index'),
                        'description' => 'Agenda della struttura con scadenze di servizio, licenze e operatività.',
                    ],
                    [
                        'title' => 'Cestino',
                        'icon' => 'ri-delete-bin-line',
                        'route' => route('cestino.index'),
                        'description' => 'Elementi eliminati recuperabili nella struttura corrente.',
                    ],
                    [
                        'title' => 'Notifiche',
                        'icon' => 'ri-notification-3-line',
                        'route' => route('notifiche.index'),
                        'description' => 'Avvisi interni, compleanni, scadenze e segnalazioni di sistema.',
                        'badge' => ((int) ($notificheTopbar['non_lette'] ?? 0)) . ' da leggere',
                    ],
                    [
                        'title' => 'Supporto online',
                        'icon' => 'ri-customer-service-2-line',
                        'route' => route('supporto.index'),
                        'description' => 'Tickets verso amministratore e supervisione del supporto operativo.',
                        'badge' => SupportTicket::query()
                            ->where('struttura_id', $strutturaDashboard->id)
                            ->whereIn('stato', ['aperto', 'in_lavorazione', 'in_attesa_struttura'])
                            ->count() . ' aperti',
                    ],
                    [
                        'title' => 'Aiuto',
                        'icon' => 'ri-question-line',
                        'route' => route('help.index'),
                        'description' => 'Manuale operativo aggiornato con le logiche della struttura e dei servizi.',
                    ],
                ],
            ];
        }

        return view('index', [
            'dashboardData' => $dashboardData,
        ]);
    }

    /*Language Translation*/
    public function lang($locale)
    {
        App::setLocale('it');
        Session::put('lang', 'it');
        Session::save();

        return redirect()->back()->with('locale', 'it');
    }

    public function updateProfile(Request $request, $id)
    {
        abort_unless((int) Auth::id() === (int) $id, 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . (int) $id],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->get('name');
        $user->email = $request->get('email');

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar =  $avatarName;
        }

        $user->update();
        if ($user) {
            Session::flash('message', 'User Details Updated successfully!');
            Session::flash('alert-class', 'alert-success');
            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "User Details Updated successfully!"
            // ], 200); // Status code here
            return redirect()->back();
        } else {
            Session::flash('message', 'Something went wrong!');
            Session::flash('alert-class', 'alert-danger');
            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "Something went wrong!"
            // ], 200); // Status code here
            return redirect()->back();

        }
    }

    public function updatePassword(Request $request, $id)
    {
        abort_unless((int) Auth::id() === (int) $id, 403);

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Your Current password does not matches with the password you provided. Please try again."
            ], 200); // Status code
        } else {
            $user = User::findOrFail($id);
            $user->password = Hash::make($request->get('password'));
            $user->update();
            if ($user) {
                Session::flash('message', 'Password updated successfully!');
                Session::flash('alert-class', 'alert-success');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Password updated successfully!"
                ], 200); // Status code here
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('alert-class', 'alert-danger');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Something went wrong!"
                ], 200); // Status code here
            }
        }
    }
}
