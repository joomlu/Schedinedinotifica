<?php

namespace App\Http\Controllers;

use App\Models\Proprietario;
use App\Models\Struttura;
use App\Services\AnagraficaStruttura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrazioneStrutturaController extends Controller
{
    private function owner(Request $request): Proprietario
    {
        $u = $request->user();
        abort_unless($u && $u->attivo && ($u->isProprietario() || ($u->isStrutturaUser() && $u->ruolo_operativo === 'proprietario')), 403);

        return Proprietario::whereKey($u->proprietario_id)->where('attivo', true)->firstOrFail();
    }

    public function create(Request $request)
    {
        $this->owner($request);
        $s = new Struttura;

        return view('struttura.nuova', ['struttura' => $s] + app(AnagraficaStruttura::class)->formData($s));
    }

    public function store(Request $request)
    {
        $owner = $this->owner($request);
        abort_if($request->hasAny(array_merge(AnagraficaStruttura::RISERVATI, AnagraficaStruttura::OPERATIVI)), 403);
        $service = app(AnagraficaStruttura::class);
        $data = $service->validate($request, null, true);
        $s = DB::transaction(function () use ($owner, $service, $data, $request) {
            Proprietario::whereKey($owner->id)->lockForUpdate()->firstOrFail();
            $payload = $service->payload($data);
            if ($request->hasFile('logo')) {
                $payload['logo'] = 'storage/'.$request->file('logo')->store('uploads/loghi', 'public');
            }

            return Struttura::create($payload + ['proprietario_id' => $owner->id, 'attiva' => false]);
        });

        return redirect()->route('struttura.edit', ['sid' => $s->id])->with('success', 'Struttura registrata. Attivazione del servizio riservata all’amministrazione.');
    }
}
