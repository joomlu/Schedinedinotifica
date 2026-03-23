<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ImpersonationLog;
use App\Models\User;
use App\Support\StrutturaCorrente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if (!$impersonator || !$impersonator->isSuperAdmin()) {
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

        return redirect()->route('root')->with('status', 'Stai impersonando ' . $target->name);
    }

    public function esci(Request $request)
    {
        $impersonatorId = $request->session()->pull('impersonator_id');
        $logId = $request->session()->pull('impersonation_log_id');
        $request->session()->forget('impersonated_id');

        if ($logId) {
            ImpersonationLog::where('id', $logId)->update(['ended_at' => now()]);
        }

        if ($impersonatorId) {
            Auth::loginUsingId($impersonatorId);
            StrutturaCorrente::clear();
            return redirect()->route('root')->with('status', 'Impersonazione terminata');
        }

        return redirect()->route('root');
    }
}
