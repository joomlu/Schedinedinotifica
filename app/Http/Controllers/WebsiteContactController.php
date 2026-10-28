<?php

namespace App\Http\Controllers;

use App\Models\CrmLead;
use App\Models\CrmLeadActivity;
use App\Support\PublicSiteCatalog;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WebsiteContactController extends Controller
{
    public function store(Request $request, PublicSiteCatalog $catalog): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'contact_form' => ['nullable', Rule::in(['commercial-v2'])],
            'structure_type' => ['required_if:contact_form,commercial-v2', 'nullable', Rule::in(['hotel', 'bnb', 'casa-vacanza', 'appartamenti', 'agriturismo', 'altro'])],
            'demo_mode' => ['exclude_unless:topic,Richiesta demo', 'nullable', Rule::in(['online', 'visita', 'da-concordare'])],
            'company' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:120'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'city' => ['required_if:contact_form,commercial-v2', 'nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required_if:contact_form,commercial-v2', 'nullable', 'string', 'max:40', Rule::when($request->input('contact_form') === 'commercial-v2', ['regex:/^(?=(?:\D*\d){5})[+0-9().\s\/-]+$/'])],
            'mobile' => ['nullable', 'string', 'max:40'],
            'website_url' => ['nullable', 'string', 'max:180'],
            'contact_time' => ['nullable', 'string', 'max:180'],
            'contact_datetime_iso' => ['nullable', 'date'],
            'contact_anytime' => ['nullable', 'boolean'],
            'topic' => ['required', 'string', Rule::in(['Richiesta demo', 'Presentazione di gruppo', 'Informazioni e personalizzazioni', 'Informazioni generali', 'Richiesta commerciale', 'Supporto e attivazione'])],
            'message' => ['nullable', 'string', 'max:5000'],
            'plan' => ['nullable', 'string', Rule::in(array_keys($catalog->plans()))],
            'max_full' => ['nullable', 'boolean'],
            'interest' => ['nullable', 'string', Rule::in(array_keys($catalog->interests()))],
            'consent' => ['required', 'accepted'],
        ], [
            'required' => 'Compila il campo :attribute.',
            'required_if' => 'Compila il campo :attribute.',
            'phone.regex' => 'Inserisci un numero di telefono valido.',
            'accepted' => 'È necessario autorizzare il contatto commerciale.',
            'email' => 'Inserisci un indirizzo email valido.',
            'string' => 'Il campo :attribute deve contenere testo.',
            'max' => 'Il campo :attribute supera la lunghezza consentita.',
            'in' => 'La selezione per :attribute non è valida.',
            'boolean' => 'La selezione per :attribute non è valida.',
            'date' => 'La data di contatto non è valida.',
        ], [
            'structure_type' => 'tipo di struttura', 'demo_mode' => 'preferenza per la demo',
            'company' => 'struttura', 'name' => 'nome e cognome', 'contact_person' => 'persona di contatto',
            'city' => 'località', 'email' => 'email', 'phone' => 'telefono', 'mobile' => 'cellulare',
            'website_url' => 'sito internet', 'contact_time' => 'preferenza oraria',
            'contact_datetime_iso' => 'data di contatto', 'contact_anytime' => 'preferenza di orario',
            'topic' => 'motivo di contatto', 'message' => 'messaggio', 'plan' => 'piano',
            'max_full' => 'Max Full', 'interest' => 'interesse', 'consent' => 'consenso',
        ]);

        if (($request->string('website')->trim()->value()) !== '') {
            throw ValidationException::withMessages([
                'website' => 'Richiesta non valida.',
            ]);
        }

        $selection = $catalog->selection($data['plan'] ?? null, (bool) ($data['max_full'] ?? false), $data['interest'] ?? null);
        $context = $selection !== '' ? [$selection] : [];
        $types = ['hotel' => 'Hotel', 'bnb' => 'B&B', 'casa-vacanza' => 'Casa vacanza', 'appartamenti' => 'Appartamenti turistici', 'agriturismo' => 'Agriturismo', 'altro' => 'Altra attività'];
        if (filled($data['structure_type'] ?? null)) {
            $context[] = 'Tipo di struttura: '.$types[$data['structure_type']];
        }
        if ($data['topic'] === 'Richiesta demo' && ($request->input('contact_form') === 'commercial-v2' || filled($data['demo_mode'] ?? null))) {
            $modes = ['online' => 'Online', 'visita' => 'Visita in struttura', 'da-concordare' => 'Da concordare'];
            $context[] = 'Preferenza demo: '.$modes[$data['demo_mode'] ?? 'da-concordare'];
            $context[] = 'La modalità viene concordata con la struttura.';
        }
        $message = trim((string) ($data['message'] ?? ''));
        $data['message'] = $context === [] ? $message : implode("\n", $context).($message !== '' ? "\n\nMessaggio del richiedente:\n".$message : '');

        $attributes = [
            'fonte' => 'sito_web',
            'struttura' => trim((string) $data['company']),
            'nome_cognome' => trim((string) $data['name']),
            'persona_contatto' => filled($data['contact_person'] ?? null) ? trim((string) $data['contact_person']) : null,
            'localita' => filled($data['city'] ?? null) ? trim((string) $data['city']) : null,
            'email' => trim((string) $data['email']),
            'telefono' => filled($data['phone'] ?? null) ? trim((string) $data['phone']) : null,
            'cellulare' => filled($data['mobile'] ?? null) ? trim((string) $data['mobile']) : null,
            'sito_web' => filled($data['website_url'] ?? null) ? trim((string) $data['website_url']) : null,
            'modalita_contatto' => trim((string) $data['topic']),
            'preferenza_contatto_label' => filled($data['contact_time'] ?? null) ? trim((string) $data['contact_time']) : null,
            'preferenza_contatto_at' => $data['contact_datetime_iso'] ?? null,
            'qualsiasi_orario' => (bool) ($data['contact_anytime'] ?? false),
            'messaggio' => trim((string) $data['message']),
            'prossimo_contatto_at' => $data['contact_datetime_iso'] ?? null,
        ];

        try {
            $saved = Cache::lock('sito-contatti-scrittura', 15)->block(5, function () use ($attributes, $data): array {
                return DB::transaction(function () use ($attributes, $data): array {
                    $existing = CrmLead::where($attributes)->where('created_at', '>=', now()->subMinutes(10))
                        ->whereHas('activities', fn ($query) => $query->where('tipo', 'richiesta_web')->where('direzione', 'entrata')->where('titolo', trim($data['topic']))->where('descrizione', $attributes['messaggio']))
                        ->lockForUpdate()->first();
                    if ($existing) {
                        return ['lead' => $existing, 'duplicate' => true];
                    }
                    $lead = CrmLead::create($attributes + ['lead_code' => $this->nextLeadCode(), 'stato' => 'nuovo']);

                    CrmLeadActivity::create([
                        'crm_lead_id' => $lead->id,
                        'tipo' => 'richiesta_web',
                        'direzione' => 'entrata',
                        'stato' => 'registrata',
                        'titolo' => trim((string) $data['topic']),
                        'descrizione' => trim((string) $data['message']),
                        'scheduled_at' => $data['contact_datetime_iso'] ?? null,
                    ]);

                    return ['lead' => $lead, 'duplicate' => false];
                });
            });
        } catch (LockTimeoutException) {
            if (! $request->expectsJson()) {
                return redirect()->route('site.contatti', [], 303)
                    ->withErrors(['request' => 'La richiesta è in elaborazione. Attendi qualche secondo prima di riprovare.'])
                    ->withInput($request->except('website'));
            }

            return response()->json(['ok' => false, 'message' => 'La richiesta è in elaborazione. Attendi qualche secondo prima di riprovare.'], 503)->header('Retry-After', '2');
        }
        $lead = $saved['lead'];

        if (! $request->expectsJson()) {
            return redirect()->route('site.contatti', [], 303)->with('sito-richiesta-registrata', true);
        }

        return response()->json([
            'ok' => true,
            'lead_id' => $lead->id,
            'duplicate' => $saved['duplicate'],
            'lead_code' => $lead->lead_code,
            'message' => 'La sua richiesta è stata elaborata correttamente.',
        ]);
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
}
