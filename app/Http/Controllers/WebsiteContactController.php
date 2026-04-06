<?php

namespace App\Http\Controllers;

use App\Models\CrmLead;
use App\Models\CrmLeadActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WebsiteContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:120'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'website_url' => ['nullable', 'string', 'max:180'],
            'contact_time' => ['nullable', 'string', 'max:180'],
            'contact_datetime_iso' => ['nullable', 'date'],
            'contact_anytime' => ['nullable', 'boolean'],
            'topic' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if (($request->string('website')->trim()->value()) !== '') {
            throw ValidationException::withMessages([
                'website' => 'Richiesta non valida.',
            ]);
        }

        $lead = DB::transaction(function () use ($data) {
            $lead = CrmLead::create([
                'lead_code' => $this->nextLeadCode(),
                'fonte' => 'sito_web',
                'stato' => 'nuovo',
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
            ]);

            CrmLeadActivity::create([
                'crm_lead_id' => $lead->id,
                'tipo' => 'richiesta_web',
                'direzione' => 'entrata',
                'stato' => 'registrata',
                'titolo' => trim((string) $data['topic']),
                'descrizione' => trim((string) $data['message']),
                'scheduled_at' => $data['contact_datetime_iso'] ?? null,
            ]);

            return $lead;
        });

        return response()->json([
            'ok' => true,
            'lead_id' => $lead->id,
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
