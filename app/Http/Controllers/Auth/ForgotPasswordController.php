<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    protected function credentials(Request $request): array
    {
        return [
            'email' => $request->input('email'),
            'attivo' => true,
        ];
    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        return redirect()
            ->route('login')
            ->with('status', 'Abbiamo inviato il link di recupero password all\'indirizzo email indicato.')
            ->with('server_alert_redirect', route('login'));
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return redirect()
            ->route('password.request')
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Non abbiamo trovato un account attivo associato a questa email.',
            ]);
    }
}
