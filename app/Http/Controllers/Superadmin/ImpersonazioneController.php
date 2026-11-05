<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ImpersonationLog;
use App\Models\User;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImpersonazioneController extends Controller
{
    public function index(Request $request)
    {
        $users = User::orderBy('ruolo')->orderBy('name')->get();
        $impersonatorId = $request->session()->get('impersonator_id');
        $impersonatedId = $request->session()->get('impersonated_id');

        return view('superadmin.impersonazione.index', [
            'users' => $users,
            'impersonatorId' => $impersonatorId,
            'impersonatedId' => $impersonatedId,
        ]);
    }

    public function impersona(Request $request, int $userId)
    {
        $impersonator = $request->user();
        if (! $impersonator || ! $impersonator->isSuperAdmin()) {
            abort(403);
        }

        if ($request->session()->has('impersonator_id')) {
            return redirect()->route('root')->with('status', 'Sei già in impersonazione. Esci prima di cambiare utente.');
        }

        $target = User::findOrFail($userId);
        $log = ImpersonationLog::create([
            'impersonator_id' => $impersonator->id,
            'impersonated_id' => $target->id,
            'started_at' => now(),
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500),
        ]);

        $request->session()->put('impersonator_id', $impersonator->id);
        $request->session()->put('impersonated_id', $target->id);
        $request->session()->put('impersonation_log_id', $log->id);
        StrutturaCorrente::setId($target->struttura_id);

        Auth::loginUsingId($target->id);
        $request->session()->regenerate();

        return redirect()->route('root')->with('status', 'Stai impersonando '.$target->name);
    }

    public function esci(Request $request)
    {
        $impersonatorId = (int) $request->session()->get('impersonator_id');
        $targetId = (int) $request->session()->get('impersonated_id');
        $logId = (int) $request->session()->get('impersonation_log_id');
        abort_unless($impersonatorId > 0 && $targetId > 0 && $logId > 0
            && (int) $request->user()?->id === $targetId, 403);

        $impersonator = DB::transaction(function () use ($impersonatorId, $targetId, $logId) {
            $log = ImpersonationLog::query()->lockForUpdate()->find($logId);
            $original = User::query()->find($impersonatorId);
            abort_unless($log && $log->ended_at === null
                && (int) $log->impersonator_id === $impersonatorId
                && (int) $log->impersonated_id === $targetId
                && $original?->isSuperAdmin() && $original->attivo, 403);
            $log->update(['ended_at' => now()]);

            return $original;
        });

        Auth::login($impersonator);
        $request->session()->forget(['impersonator_id', 'impersonated_id', 'impersonation_log_id', 'struttura_corrente_id']);
        $request->session()->regenerate();
        StrutturaCorrente::clear();

        return redirect()->route('root')->with('status', 'Impersonazione terminata');
    }
}
