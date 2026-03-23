<?php

namespace App\Http\Controllers;

use App\Models\Struttura;
use App\Models\StrutturaAccesso;
use App\Models\StrutturaAuditLog;
use App\Models\StrutturaComanda;
use App\Models\Customers;
use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\User;
use App\Models\WebCheckinRichiesta;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GestioneOperativaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $struttura = $this->resolveStruttura($user);
        $canManage = $user->canManageGestioneOperativa($struttura->id);
        $sharedUsername = User::query()
            ->where('struttura_id', $struttura->id)
            ->whereNotNull('username')
            ->value('username') ?: ('struttura' . $struttura->id);

        $utenti = User::query()
            ->where('struttura_id', $struttura->id)
            ->orderByRaw("case when ruolo_operativo = 'proprietario' then 0 else 1 end")
            ->orderBy('display_name')
            ->orderBy('name')
            ->get();

        $comande = StrutturaComanda::query()
            ->with(['mittente', 'destinatario'])
            ->orderByRaw("case when stato = 'da_leggere' then 0 when stato = 'letta' then 1 else 2 end")
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $accessi = StrutturaAccesso::query()
            ->with('utente')
            ->latest('entrata_at')
            ->limit(50)
            ->get();

        $auditLogs = StrutturaAuditLog::query()
            ->with('utente')
            ->latest('created_at')
            ->limit(80)
            ->get()
            ->map(function (StrutturaAuditLog $log) {
                $log->sezione_label = $this->sezioneLabel($log->route_name);
                $log->descrizione = $this->descrizioneBreve($log->route_name, $log->metodo, $log->descrizione);
                $log->dettaglio_context = $this->resolveAuditContext($log);
                return $log;
            });

        return view('gestione-operativa.index', [
            'struttura' => $struttura,
            'utenteCorrente' => $user,
            'canManage' => $canManage,
            'sharedUsername' => $sharedUsername,
            'utenti' => $utenti,
            'comande' => $comande,
            'accessi' => $accessi,
            'auditLogs' => $auditLogs,
            'activeTab' => (string) $request->query('tab', $canManage ? 'utenti' : 'profilo'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ], [
            'name.required' => 'Inserisci nome e cognome.',
            'display_name.required' => 'Inserisci il nome da vedere in alto.',
            'email.email' => 'Inserisci un indirizzo email valido.',
            'email.unique' => 'Questa email e gia in uso.',
        ]);

        $user->fill([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? $user->email,
        ]);

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('/images/'), $avatarName);
            $user->avatar = $avatarName;
        }

        $user->save();

        return redirect()->route('gestione.operativa.index', ['tab' => 'profilo'])->with('success', 'Profilo aggiornato.');
    }

    public function updateMyPassword(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Inserisci la password attuale.',
            'password.required' => 'Inserisci la nuova password.',
            'password.min' => 'La nuova password deve avere almeno 8 caratteri.',
            'password.confirmed' => 'La conferma password non coincide.',
        ]);

        if (!Hash::check($data['current_password'], (string) $user->password)) {
            return redirect()
                ->route('gestione.operativa.index', ['tab' => 'profilo'])
                ->withErrors(['current_password' => 'La password attuale non coincide.']);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return redirect()->route('gestione.operativa.index', ['tab' => 'profilo'])->with('success', 'Password personale aggiornata.');
    }

    public function storeUtente(Request $request)
    {
        $struttura = $this->resolveStruttura($request->user());
        $this->ensureCanManage($request->user(), $struttura->id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:120'],
            'shared_username' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'ruolo_operativo' => ['required', Rule::in(['proprietario', 'reception'])],
            'password' => ['required', 'string', 'min:8'],
            'attivo' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Inserisci nome e cognome.',
            'display_name.required' => 'Inserisci il nome da vedere in alto.',
            'shared_username.required' => 'Inserisci il nome di accesso della struttura.',
            'ruolo_operativo.required' => 'Seleziona il ruolo.',
            'password.required' => 'Inserisci la password personale.',
            'password.min' => 'La password personale deve avere almeno 8 caratteri.',
            'email.email' => 'Inserisci un indirizzo email valido.',
            'email.unique' => 'Questa email e gia in uso.',
        ]);

        User::query()->where('struttura_id', $struttura->id)->update([
            'username' => $data['shared_username'],
        ]);

        User::create([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'username' => $data['shared_username'],
            'email' => $data['email'] ?: ('operativo.' . uniqid() . '@' . $struttura->id . '.local'),
            'telefono' => $data['telefono'] ?? null,
            'password' => Hash::make($data['password']),
            'ruolo' => 'struttura_user',
            'ruolo_operativo' => $data['ruolo_operativo'],
            'struttura_id' => $struttura->id,
            'attivo' => $request->boolean('attivo', true),
            'avatar' => '',
        ]);

        return redirect()->route('gestione.operativa.index', ['tab' => 'utenti'])->with('success', 'Persona creata.');
    }

    public function updateUtente(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request->user());
        $this->ensureCanManage($request->user(), $struttura->id);
        $utente = User::query()->where('struttura_id', $struttura->id)->findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:120'],
            'shared_username' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($utente->id)],
            'telefono' => ['nullable', 'string', 'max:40'],
            'ruolo_operativo' => ['required', Rule::in(['proprietario', 'reception'])],
            'attivo' => ['nullable', 'boolean'],
        ]);

        User::query()->where('struttura_id', $struttura->id)->update([
            'username' => $data['shared_username'],
        ]);

        $utente->fill([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'username' => $data['shared_username'],
            'email' => $data['email'] ?: $utente->email,
            'telefono' => $data['telefono'] ?? null,
            'ruolo_operativo' => $data['ruolo_operativo'],
            'attivo' => $request->boolean('attivo', false),
        ])->save();

        return redirect()->route('gestione.operativa.index', ['tab' => 'utenti'])->with('success', 'Persona aggiornata.');
    }

    public function resetPassword(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request->user());
        $this->ensureCanManage($request->user(), $struttura->id);
        $utente = User::query()->where('struttura_id', $struttura->id)->findOrFail($id);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ], [
            'password.required' => 'Inserisci la nuova password.',
            'password.min' => 'La nuova password deve avere almeno 8 caratteri.',
        ]);

        $utente->forceFill(['password' => Hash::make($data['password'])])->save();

        return redirect()->route('gestione.operativa.index', ['tab' => 'utenti'])->with('success', 'Password aggiornata.');
    }

    public function storeComanda(Request $request)
    {
        $struttura = $this->resolveStruttura($request->user());
        $request->validate([
            'titolo' => ['required', 'string', 'max:160'],
            'messaggio' => ['required', 'string', 'max:5000'],
            'priorita' => ['required', Rule::in(['bassa', 'normale', 'alta'])],
            'destinatario_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('struttura_id', $struttura->id))],
        ]);

        StrutturaComanda::create([
            'struttura_id' => $struttura->id,
            'mittente_id' => $request->user()->id,
            'destinatario_id' => $request->input('destinatario_id') ?: null,
            'titolo' => $request->input('titolo'),
            'messaggio' => $request->input('messaggio'),
            'priorita' => $request->input('priorita', 'normale'),
            'stato' => 'da_leggere',
        ]);

        return $this->redirectAfterComandaAction($request, 'Consegna salvata.');
    }

    public function markComandaRead(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request->user());
        $comanda = StrutturaComanda::query()->where('struttura_id', $struttura->id)->findOrFail($id);
        $comanda->forceFill([
            'stato' => 'letta',
            'letto_at' => now(),
        ])->save();

        return $this->redirectAfterComandaAction($request, 'Consegna segnata come vista.');
    }

    public function closeComanda(Request $request, int $id)
    {
        $struttura = $this->resolveStruttura($request->user());
        $comanda = StrutturaComanda::query()->where('struttura_id', $struttura->id)->findOrFail($id);
        $comanda->forceFill([
            'stato' => 'chiusa',
            'chiuso_at' => now(),
            'letto_at' => $comanda->letto_at ?: now(),
        ])->save();

        return $this->redirectAfterComandaAction($request, 'Consegna chiusa.');
    }

    private function resolveStruttura(User $user): Struttura
    {
        if ($user->struttura_id) {
            StrutturaCorrente::setId($user->struttura_id);
            return Struttura::query()->findOrFail($user->struttura_id);
        }

        $strutturaId = StrutturaCorrente::getId();
        abort_unless($strutturaId, 403, 'Struttura non selezionata.');
        return Struttura::query()->findOrFail($strutturaId);
    }

    private function ensureCanManage(User $user, int $strutturaId): void
    {
        abort_unless($user->canManageGestioneOperativa($strutturaId), 403, 'Operazione riservata al proprietario della struttura.');
    }

    private function redirectAfterComandaAction(Request $request, string $message)
    {
        $redirect = (string) $request->input('redirect_to', '');

        return match ($redirect) {
            'notifiche' => redirect()->route('notifiche.index')->with('success', $message),
            default => redirect()->route('gestione.operativa.index', ['tab' => 'messaggi'])->with('success', $message),
        };
    }

    private function sezioneLabel(?string $routeName): string
    {
        $routeName = (string) $routeName;

        return match (true) {
            str_starts_with($routeName, 'customer.') => 'Clienti',
            str_starts_with($routeName, 'schedina.') => 'Schedine',
            str_starts_with($routeName, 'arrivals.') || str_starts_with($routeName, 'arrival.') || str_contains($routeName, 'a_schedina') => 'Arrivi',
            str_starts_with($routeName, 'componenti.') => 'Componenti schedina',
            str_starts_with($routeName, 'web_checkin.') => 'Web Check-in',
            str_starts_with($routeName, 'questura.') => 'Invio Questura',
            str_starts_with($routeName, 'istat.tabella_a.') => 'Tabella A Emilia-Romagna',
            str_starts_with($routeName, 'gestione.operativa.') => 'Persone e consegne',
            str_starts_with($routeName, 'tassa_di_soggiorno.') || str_starts_with($routeName, 'tassa_esenzioni.') => 'Tassa di soggiorno',
            str_starts_with($routeName, 'tipo_') || str_starts_with($routeName, 'rilasciato.') || str_starts_with($routeName, 'gruppo.') => 'Configurazioni',
            default => 'Sistema',
        };
    }

    private function descrizioneBreve(?string $routeName, ?string $method, string $fallback): string
    {
        $routeName = (string) $routeName;
        $method = strtoupper((string) $method);

        if (str_starts_with($routeName, 'customer.')) {
            return match (true) {
                str_contains($routeName, '.store') => 'Ha creato un cliente',
                str_contains($routeName, '.update') => 'Ha modificato un cliente',
                str_contains($routeName, '.destroy') => 'Ha eliminato un cliente',
                default => 'Ha lavorato in Clienti',
            };
        }

        if (str_starts_with($routeName, 'schedina.')) {
            return match (true) {
                str_contains($routeName, '.store') => 'Ha creato una schedina',
                str_contains($routeName, '.update') => 'Ha modificato una schedina',
                str_contains($routeName, '.destroy') => 'Ha eliminato una schedina',
                default => 'Ha lavorato in Schedine',
            };
        }

        if (str_starts_with($routeName, 'arrivals.') || str_starts_with($routeName, 'arrival.') || str_contains($routeName, 'a_schedina')) {
            return match (true) {
                str_contains($routeName, '.store') => 'Ha creato un arrivo',
                str_contains($routeName, '.destroy') => 'Ha eliminato un arrivo',
                str_contains($routeName, 'a_schedina') => 'Ha convertito un arrivo in schedina',
                default => 'Ha lavorato in Arrivi',
            };
        }

        if (str_starts_with($routeName, 'componenti.')) {
            return match (true) {
                str_contains($routeName, '.store') => 'Ha aggiunto un componente',
                str_contains($routeName, '.update') => 'Ha modificato un componente',
                str_contains($routeName, '.destroy') => 'Ha eliminato un componente',
                default => 'Ha lavorato nei componenti della schedina',
            };
        }

        if (str_starts_with($routeName, 'web_checkin.')) {
            return match (true) {
                str_contains($routeName, '.store') => 'Ha creato un Web Check-in',
                str_contains($routeName, '.update') => 'Ha modificato un Web Check-in',
                str_contains($routeName, '.destroy') => 'Ha eliminato un Web Check-in',
                str_contains($routeName, '.convert') || str_contains($routeName, '.import') => 'Ha importato un Web Check-in',
                default => 'Ha lavorato in Web Check-in',
            };
        }

        if (str_starts_with($routeName, 'questura.')) {
            return match (true) {
                str_contains($routeName, '.verify') => 'Ha verificato l invio Questura',
                str_contains($routeName, '.send') => 'Ha inviato a Questura',
                str_contains($routeName, '.download') => 'Ha scaricato il file Questura',
                default => 'Ha lavorato in Questura',
            };
        }

        if (str_starts_with($routeName, 'istat.tabella_a.')) {
            return match (true) {
                str_contains($routeName, '.verify') => 'Ha verificato l invio Tabella A Emilia-Romagna',
                str_contains($routeName, '.send') => 'Ha inviato la Tabella A Emilia-Romagna',
                str_contains($routeName, '.download') => 'Ha scaricato il file XML Tabella A Emilia-Romagna',
                default => 'Ha lavorato in Tabella A Emilia-Romagna',
            };
        }

        if (str_starts_with($routeName, 'gestione.operativa.')) {
            return match (true) {
                str_contains($routeName, '.utenti.store') => 'Ha creato un utente',
                str_contains($routeName, '.utenti.update') => 'Ha modificato un utente',
                str_contains($routeName, '.utenti.password') => 'Ha cambiato una password',
                str_contains($routeName, '.comande.store') => 'Ha lasciato una consegna',
                str_contains($routeName, '.comande.read') => 'Ha segnato una consegna come vista',
                str_contains($routeName, '.comande.close') => 'Ha chiuso una consegna',
                default => 'Ha lavorato in Persone e consegne',
            };
        }

        if (str_starts_with($routeName, 'tassa_di_soggiorno.') || str_starts_with($routeName, 'tassa_esenzioni.')) {
            return 'Ha lavorato in Tassa di soggiorno';
        }

        if (str_starts_with($routeName, 'tipo_') || str_starts_with($routeName, 'rilasciato.') || str_starts_with($routeName, 'gruppo.')) {
            return match (true) {
                str_contains($routeName, '.store') => 'Ha creato una configurazione',
                str_contains($routeName, '.update') => 'Ha modificato una configurazione',
                str_contains($routeName, '.destroy') => 'Ha eliminato una configurazione',
                default => 'Ha lavorato in Configurazioni',
            };
        }

        return match (true) {
            str_contains($routeName, '.store') => 'Ha creato un elemento',
            str_contains($routeName, '.update') => 'Ha modificato un elemento',
            str_contains($routeName, '.destroy') => 'Ha eliminato un elemento',
            str_contains($routeName, '.verify') => 'Ha verificato un invio',
            str_contains($routeName, '.send') => 'Ha inviato dei dati',
            str_contains($routeName, '.download') => 'Ha scaricato un file',
            str_contains($routeName, '.restore') => 'Ha ripristinato un elemento dal cestino',
            str_contains($routeName, '.read') => 'Ha segnato un messaggio come visto',
            str_contains($routeName, '.close') => 'Ha chiuso un messaggio',
            str_contains($routeName, '.password') => 'Ha cambiato una password',
            default => $fallback ?: 'Ha lavorato nel programma',
        };
    }

    private function resolveAuditContext(StrutturaAuditLog $log): array
    {
        $routeName = (string) $log->route_name;
        $entityId = $log->entita_id ? (int) $log->entita_id : null;

        if (!$entityId) {
            return [];
        }

        if (str_starts_with($routeName, 'schedina.') || str_starts_with($routeName, 'arrivals.') || str_starts_with($routeName, 'arrival.') || str_contains($routeName, 'a_schedina')) {
            $schedina = Schedina::query()->withoutGlobalScopes()->find($entityId);
            if (!$schedina) {
                return [];
            }

            return [
                'record_type' => 'Schedina',
                'scheda' => $schedina->scheda,
                'origine' => $this->auditCircuitOrigin($schedina),
                'ospite' => trim(($schedina->surname ?? '') . ' ' . ($schedina->name ?? '')) ?: null,
                'arrivo' => $schedina->arrive,
                'partenza' => $schedina->departure,
                'persone' => $schedina->cant_people,
            ];
        }

        if (str_starts_with($routeName, 'web_checkin.')) {
            $richiesta = WebCheckinRichiesta::query()->with('schedina')->find($entityId);
            if (!$richiesta) {
                return [];
            }

            return [
                'record_type' => 'Web Check-in',
                'codice' => $richiesta->codice,
                'scheda' => $richiesta->schedina?->scheda,
                'origine' => 'Web Check-in',
                'ospite' => $richiesta->nome_referente,
                'arrivo' => optional($richiesta->arrivo)->format('Y-m-d'),
                'partenza' => optional($richiesta->partenza)->format('Y-m-d'),
                'persone' => $richiesta->quantita_persone,
                'stato' => $richiesta->stato,
            ];
        }

        if (str_starts_with($routeName, 'customer.')) {
            $cliente = Customers::query()->find($entityId);
            if (!$cliente) {
                return [];
            }

            return [
                'record_type' => 'Cliente',
                'codice' => $cliente->numero_cliente,
                'ospite' => trim(($cliente->surname ?? '') . ' ' . ($cliente->name ?? '')) ?: null,
                'origine' => 'Clienti',
            ];
        }

        if (str_starts_with($routeName, 'componenti.')) {
            $componente = Componenti::query()->with('schedina')->find($entityId);
            if (!$componente) {
                return [];
            }

            return [
                'record_type' => 'Componente',
                'scheda' => $componente->schedina?->scheda,
                'ospite' => trim(($componente->surname ?? '') . ' ' . ($componente->name ?? '')) ?: null,
                'origine' => $componente->schedina ? $this->auditCircuitOrigin($componente->schedina) : null,
            ];
        }

        return [];
    }

    private function auditCircuitOrigin(Schedina $schedina): string
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
}
