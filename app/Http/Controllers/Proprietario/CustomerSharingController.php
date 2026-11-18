<?php

namespace App\Http\Controllers\Proprietario;

use App\Http\Controllers\Controller;
use App\Services\CustomerSharing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class CustomerSharingController extends Controller
{
    public function __construct(private readonly CustomerSharing $sharing) {}

    private function owner(Request $request): int
    {
        $user = $request->user();
        abort_unless($user && $user->attivo && $user->isProprietario() && $user->proprietario_id, 403);
        return (int) $user->proprietario_id;
    }

    public function index(Request $request)
    {
        $owner = $this->owner($request);
        $installed = $this->sharing->available();
        $structures = DB::table('struttura')->where('proprietario_id', $owner)->where('attiva', true)->whereNull('deleted_at')->orderBy('nome_struttura')->get(['id', 'nome_struttura']);
        $groups = $installed ? DB::table('customer_sharing_groups')->where('proprietario_id', $owner)->where('stato', 'attivo')->orderBy('id')->get() : collect();
        // Spegnere la creazione di gruppi non nasconde gruppi già operativi.
        $available = $installed && (config('customer_sharing.enabled', false) || $groups->isNotEmpty());
        foreach ($groups as $group) {
            $group->structures = DB::table('struttura')->whereIn('id', $this->sharing->members((int) $group->id))->orderBy('nome_struttura')->get(['id', 'nome_struttura']);
            $group->people_count = DB::table('customer_persons')->where('group_id', $group->id)->whereNull('merged_into_id')->count();
            $group->candidates = $this->sharing->candidates((int) $group->id, $request->boolean('revisionati'));
            $group->residences = $this->sharing->residences((int) $group->id);
            $group->merges = DB::table('customer_sharing_operations')->where('group_id', $group->id)->where('tipo', 'fusione')->latest('id')->limit(10)->get(['id', 'created_at']);
        }
        $packages = $this->sharing->available() ? DB::table('customer_sharing_packages as p')->join('struttura as s', 's.id', '=', 'p.struttura_id')
            ->where('s.proprietario_id', $owner)->select('s.id', 's.nome_struttura')->distinct()->get() : collect();
        return response()->view('proprietario.clienti-condivisione', compact('structures', 'groups', 'available', 'packages'))
            ->header('Cache-Control', 'no-store');
    }

    public function preview(Request $request)
    {
        $owner = $this->owner($request);
        abort_unless($this->sharing->available(), 409, 'Schema condivisione non ancora installato.');
        $data = $request->validate(['nome' => ['required', 'string', 'max:120'], 'group_id' => ['nullable', 'integer', 'min:1'],
            'version' => ['nullable', 'integer', 'min:1'], 'strutture' => ['required', 'array', 'min:2', 'max:200'], 'strutture.*' => ['required', 'integer', 'min:1', 'distinct']]);
        $ids = array_map('intval', $data['strutture']); sort($ids);
        $structures = DB::table('struttura')->whereIn('id', $ids)->where('proprietario_id', $owner)->where('attiva', true)->whereNull('deleted_at')->orderBy('id')->get(['id', 'nome_struttura']);
        abort_unless($structures->pluck('id')->map(fn ($id) => (int) $id)->all() === $ids, 403);
        if (!empty($data['group_id'])) {
            $group = DB::table('customer_sharing_groups')->where('id', $data['group_id'])->where('proprietario_id', $owner)->where('stato', 'attivo')->first();
            abort_unless($group, 404);
            abort_unless((int) $group->version === (int) ($data['version'] ?? 0), 409);
        }
        $token = bin2hex(random_bytes(24));
        $request->session()->put('customer_sharing_preview', ['token' => $token, 'owner' => $owner, 'user' => $request->user()->id,
            'ids' => $ids, 'name' => $data['nome'], 'group' => $data['group_id'] ?? null, 'version' => $data['version'] ?? 0,
            'fingerprint' => $this->sharing->fingerprint($ids), 'expires' => now()->addMinutes(15)->timestamp]);
        $count = DB::table('clienti')->whereIn('struttura_id', $ids)->count();
        return response()->view('proprietario.clienti-condivisione-preview', compact('structures', 'count', 'token'))
            ->header('Cache-Control', 'no-store');
    }

    public function store(Request $request)
    {
        $owner = $this->owner($request);
        $request->validate(['token' => ['required', 'string'], 'conferma' => ['accepted']]);
        $p = $request->session()->get('customer_sharing_preview');
        abort_unless($p && $p['owner'] === $owner && $p['user'] === $request->user()->id && $p['expires'] >= now()->timestamp && hash_equals($p['token'], $request->input('token')), 409, 'Riepilogo scaduto o non valido. Ripeti la verifica.');
        $this->sharing->configure($request->user(), $p['group'] ? (int) $p['group'] : null, (int) $p['version'], $p['name'], $p['ids'], $p['fingerprint']);
        $request->session()->forget('customer_sharing_preview');
        return redirect()->route('proprietario.clienti-condivisione.index')->with('success', 'Condivisione aggiornata. Le identità certe sono collegate; verifica i possibili duplicati rimasti.');
    }

    public function separate(Request $request, int $group)
    {
        $this->owner($request);
        $data = $request->validate(['struttura_id' => ['required', 'integer', 'min:1'], 'version' => ['required', 'integer', 'min:1'], 'conferma' => ['accepted'], 'consegna' => ['required', 'boolean']]);
        $this->sharing->separate($request->user(), $group, (int) $data['struttura_id'], (int) $data['version'], $request->boolean('consegna'));
        return redirect()->route('proprietario.clienti-condivisione.index')->with('success', 'Struttura separata. Archivio e storico locali conservati, accesso al gruppo revocato e aggiornamenti indipendenti. '.($request->boolean('consegna') ? 'Pacchetto creato.' : 'Nessun pacchetto creato.'));
    }

    public function merge(Request $request, int $group)
    {
        $this->owner($request);
        $data = $request->validate(['principale' => ['required', 'integer', 'min:1'], 'secondaria' => ['required', 'integer', 'min:1', 'different:principale'],
            'principale_scelta' => ['required', 'integer', 'min:1'], 'versione_principale' => ['required', 'integer', 'min:1'], 'versione_secondaria' => ['required', 'integer', 'min:1'], 'conferma' => ['accepted']]);
        abort_unless(in_array((int) $data['principale_scelta'], [(int) $data['principale'], (int) $data['secondaria']], true), 422);
        if ((int) $data['principale_scelta'] === (int) $data['secondaria']) {
            [$data['principale'], $data['secondaria']] = [$data['secondaria'], $data['principale']];
            [$data['versione_principale'], $data['versione_secondaria']] = [$data['versione_secondaria'], $data['versione_principale']];
        }
        $this->sharing->merge($request->user(), $group, (int) $data['principale'], (int) $data['secondaria'], (int) $data['versione_principale'], (int) $data['versione_secondaria']);
        return redirect()->route('proprietario.clienti-condivisione.index')->with('success', 'Schede riunite. Codici, versioni e soggiorni precedenti sono conservati.');
    }

    public function undo(Request $request, int $group)
    {
        $this->owner($request);
        $data = $request->validate(['operazione' => ['required', 'integer', 'min:1'], 'conferma' => ['accepted']]);
        $this->sharing->undoMerge($request->user(), $group, (int) $data['operazione']);
        return redirect()->route('proprietario.clienti-condivisione.index')->with('success', 'Fusione annullata e schede ripristinate.');
    }

    public function residence(Request $request, int $group)
    {
        $this->owner($request);
        $data = $request->validate(['persona' => ['required', 'integer', 'min:1'], 'residenza' => ['required', 'integer', 'min:1'], 'version' => ['required', 'integer', 'min:1'], 'conferma' => ['accepted']]);
        $this->sharing->confirmResidence($request->user(), $group, (int) $data['persona'], (int) $data['residenza'], (int) $data['version']);
        return redirect()->route('proprietario.clienti-condivisione.index')->with('success', 'Residenza confermata; data e autore registrati.');
    }

    public function package(Request $request, int $structure)
    {
        $owner = $this->owner($request);
        abort_unless($this->sharing->available(), 404);
        abort_unless(DB::table('struttura')->where('id', $structure)->where('proprietario_id', $owner)->exists(), 404);
        $packages = DB::table('customer_sharing_packages')->where('struttura_id', $structure)->orderBy('id')->get()->map(fn ($p) => [
            'cliente_locale' => $p->customer_id, 'separazione_del' => $p->created_at, 'dati_conservati' => $this->sharing->decode($p->data)]);
        return response()->json(['struttura_id' => $structure, 'pacchetti' => $packages])->header('Cache-Control', 'no-store')
            ->header('Content-Disposition', 'attachment; filename="pacchetto-clienti-struttura-'.$structure.'.json"');
    }

    public function sources(Request $request, int $group)
    {
        $this->owner($request);
        abort_unless($this->sharing->available(), 404);
        return response()->json(['fonti' => $this->sharing->sourceArchive($request->user(), $group)])
            ->header('Cache-Control', 'no-store')->header('Content-Disposition', 'attachment; filename="provenienze-clienti-gruppo-'.$group.'.json"');
    }

    public function keepSeparate(Request $request, int $group)
    {
        $this->owner($request);
        $data = $request->validate(['prima' => ['required', 'integer', 'min:1'], 'seconda' => ['required', 'integer', 'min:1', 'different:prima'],
            'versione_prima' => ['required', 'integer', 'min:1'], 'versione_seconda' => ['required', 'integer', 'min:1'], 'conferma' => ['accepted']]);
        $this->sharing->keepSeparate($request->user(), $group, (int) $data['prima'], (int) $data['seconda'], (int) $data['versione_prima'], (int) $data['versione_seconda']);
        return redirect()->route('proprietario.clienti-condivisione.index')->with('success', 'Schede mantenute separate per questa revisione. La scelta resta consultabile e sarà riproposta se cambiano i dati.');
    }
}
