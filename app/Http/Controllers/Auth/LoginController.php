<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\AccessoOperativoService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        logout as performLogout;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'login';
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        $login = (string) $request->input('login');
        $password = (string) $request->input('password');

        $users = User::query()
            ->where('username', $login)
            ->where('attivo', true)
            ->orderByDesc('ruolo_operativo')
            ->get();

        foreach ($users as $user) {
            if (Hash::check($password, $user->password)) {
                Auth::login($user, $request->boolean('remember'));
                return true;
            }
        }

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return Auth::attempt([
                'email' => $login,
                'password' => $password,
                'attivo' => true,
            ], $request->boolean('remember'));
        }

        return false;
    }

    protected function authenticated(Request $request, $user)
    {
        $this->ensureStrutturaAccessAllowed($request, $user);
        app(AccessoOperativoService::class)->open($user, $request);
    }

    public function logout(Request $request)
    {
        app(AccessoOperativoService::class)->close($request->user(), $request);

        return $this->performLogout($request);
    }

    protected function ensureStrutturaAccessAllowed(Request $request, User $user): void
    {
        if (!$user->isStrutturaUser()) {
            return;
        }

        $struttura = $user->struttura;
        if (!$struttura) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Struttura non disponibile. Contatta l\'amministratore.',
            ]);
        }

        if (!$struttura->attiva) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => trim((string) ($struttura->messaggio_offline ?: 'Il servizio non è disponibile. Contatta l\'amministratore.')),
            ]);
        }

        if (!$struttura->servizioAttivo()) {
            Auth::logout();

            $defaultExpiredMessage = $struttura->scadenza_servizio
                ? 'Il servizio non è attivo dal '.$struttura->scadenza_servizio->format('d/m/Y').'. Regolarizza il pagamento o contatta l\'amministratore.'
                : 'Il servizio non è attivo. Regolarizza il pagamento o contatta l\'amministratore.';

            throw ValidationException::withMessages([
                'login' => trim((string) ($struttura->messaggio_offline ?: $defaultExpiredMessage)),
            ]);
        }

        if (in_array((string) $struttura->avviso, ['sospeso', 'inattivo'], true)) {
            Auth::logout();
            $defaultMessage = $struttura->avviso === 'sospeso'
                ? 'Il servizio è sospeso. Contatta l\'amministratore.'
                : 'Il servizio è inattivo. Contatta l\'amministratore.';

            throw ValidationException::withMessages([
                'login' => trim((string) ($struttura->messaggio_avviso ?: $defaultMessage)),
            ]);
        }
    }
}
