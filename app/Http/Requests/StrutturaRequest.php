<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TipologiaStruttura;
use App\Models\Classificazione;
use App\Models\GeoNazione;
use App\Models\GeoRegione;
use App\Models\GeoProvincia;
use App\Models\GeoComune;
use Carbon\Carbon;

class StrutturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizeCode = function ($value) {
            if ($value === null) {
                return null;
            }

            $value = trim((string) $value);
            if ($value === '') {
                return null;
            }

            return mb_strtoupper(preg_replace('/\s+/', '', $value));
        };

        $geoNazione = $this->input('nazione');
        $geoRegione = $this->input('regione');
        $geoProvincia = $this->input('provincia');
        $geoComune = $this->input('citta');

        if (is_numeric($geoNazione)) {
            $nazione = GeoNazione::find((int) $geoNazione);
            $geoNazione = $nazione?->nome ?? $geoNazione;
        }

        if (is_numeric($geoRegione)) {
            $regione = GeoRegione::find((int) $geoRegione);
            $geoRegione = $regione?->nome ?? $geoRegione;
        }

        if (is_numeric($geoProvincia)) {
            $provincia = GeoProvincia::find((int) $geoProvincia);
            $geoProvincia = $provincia?->sigla ?: ($provincia?->nome ?? $geoProvincia);
        }

        if (is_numeric($geoComune)) {
            $comune = GeoComune::find((int) $geoComune);
            $geoComune = $comune?->nome ?? $geoComune;
        }

        $this->merge([
            'nazione' => $geoNazione,
            'regione' => $geoRegione,
            'provincia' => $geoProvincia,
            'citta' => $geoComune,
            'cir' => $normalizeCode($this->input('cir')),
            'cin' => $normalizeCode($this->input('cin')),
            'codice_unico' => $normalizeCode($this->input('codice_unico')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nome_struttura' => ['required', 'string', 'max:150', "regex:/^[\\pL\\pN\\s\\.\\-'&\\/]+$/u"],
            'tipologia_generale_id' => 'required|exists:tipologie_generali,id',
            'tipologia_struttura_id' => 'required|exists:tipologie_struttura,id',
            'classificazione_id' => 'nullable|exists:classificazioni,id',
            'tipo_apertura' => 'required|in:Annuale,Stagionale',
            'nazione' => ['required', 'string', 'max:100', "regex:/^[\\pL\\s\\-']+$/u"],
            'regione' => ['required', 'string', 'max:100', "regex:/^[\\pL\\s\\-']+$/u"],
            'provincia' => ['required', 'string', 'max:100', "regex:/^[\\pL\\s\\-\\.']+$/u"],
            'citta' => ['required', 'string', 'max:150', "regex:/^[\\pL\\s\\-\\.']+$/u"],
            'indirizzo' => ['required', 'string', 'max:191', "regex:/^[\\pL\\pN\\s\\-\\.'\\/]+$/u"],
            'cap' => ['required', 'string', 'max:10', 'regex:/^[0-9]{4,10}$/'],
            'telefono' => ['required', 'string', 'max:20', 'regex:/^[0-9\\+\\s\\-\\(\\)]+$/'],
            'email' => 'required|email|max:191',
            'latitudine' => 'required|numeric',
            'longitudine' => 'required|numeric',
            'camere_disponibili' => 'nullable|integer|min:0',
            'letti_disponibili' => 'nullable|integer|min:0',
            'letti_agg' => 'nullable|integer|min:0',
            'camere_reali_enabled' => 'nullable|boolean',
            // altri campi
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'cir' => ['nullable', 'string', 'max:50', 'regex:/^[A-Z0-9\\-\\/]+$/'],
            'data_apertura' => 'nullable|date',
            'data_chiusura' => 'nullable|date',
            'logo_citta' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'zona' => ['nullable', 'string', 'max:100', "regex:/^[\\pL\\pN\\s\\-\\.']+$/u"],
            'localita' => ['nullable', 'string', 'max:150', "regex:/^[\\pL\\pN\\s\\-\\.']+$/u"],
            'numero_civico' => ['nullable', 'string', 'max:20', 'regex:/^[\\pL\\pN\\s\\-\\/]+$/u'],
            'ragione_sociale' => ['nullable', 'string', 'max:191', "regex:/^[\\pL\\pN\\s\\.\\-'&\\/]+$/u"],
            'partita_iva' => ['nullable', 'string', 'size:11', 'regex:/^[0-9]{11}$/'],
            'codice_fiscale' => ['nullable', 'string', 'max:16', 'regex:/^(?:[A-Z0-9]{16}|[0-9]{11})$/i'],
            'cin' => ['nullable', 'string', 'max:50', 'regex:/^[A-Z0-9\\-\\/]+$/'],
            'codice_unico' => ['nullable', 'string', 'max:7', 'regex:/^[A-Z0-9]+$/'],
            'istat_username' => 'nullable|string|max:100',
            'istat_password' => 'nullable|string|max:100',
            'istat_codice_struttura' => 'nullable|string|max:50',
            'istat_ws_url' => 'nullable|url|max:191',
            'istat_ws_simulazione' => 'nullable|boolean',
            'questura_username' => 'nullable|string|max:100',
            'questura_password' => 'nullable|string|max:100',
            'questura_wskey' => 'nullable|string|max:191',
            'questura_codici' => 'nullable|string|max:191',
            'questura_puk' => 'nullable|string|max:191',
            'questura_ws_simulazione' => 'nullable|boolean',
            'telefono_secondario' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\\+\\s\\-\\(\\)]+$/'],
            'fax' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\\+\\s\\-\\(\\)]+$/'],
            'sito_web' => 'nullable|url|max:191',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data_apertura = $this->input('data_apertura');
            $data_chiusura = $this->input('data_chiusura');
            if ($data_apertura && $data_chiusura) {
                try {
                    $apertura = Carbon::parse($data_apertura);
                    $chiusura = Carbon::parse($data_chiusura);
                    if ($chiusura->lt($apertura)) {
                        $validator->errors()->add('data_chiusura', 'La data di chiusura non può essere precedente alla data di apertura.');
                    }
                } catch (\Throwable $e) {
                    // Ignorato: la validazione "date" nelle rules gestisce già il formato non valido.
                }
            }

            $generaleId = $this->input('tipologia_generale_id');
            $strutturaId = $this->input('tipologia_struttura_id');
            $classificazioneId = $this->input('classificazione_id');

            if ($generaleId && $strutturaId) {
                $belongs = TipologiaStruttura::where('id', $strutturaId)
                    ->where('tipologia_generale_id', $generaleId)
                    ->exists();

                if (!$belongs) {
                    $validator->errors()->add(
                        'tipologia_struttura_id',
                        'La tipologia struttura selezionata non appartiene alla tipologia generale scelta.'
                    );
                }
            }

            if ($strutturaId && $classificazioneId) {
                $isAllowed = Classificazione::where('id', $classificazioneId)
                    ->whereHas('tipologieStruttura', function ($q) use ($strutturaId) {
                        $q->where('tipologie_struttura.id', $strutturaId);
                    })
                    ->exists();

                if (!$isAllowed) {
                    $validator->errors()->add(
                        'classificazione_id',
                        'La classificazione selezionata non è valida per la tipologia struttura scelta.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'nome_struttura.regex' => 'Il nome struttura puo contenere lettere, numeri e segni semplici di uso comune.',
            'nazione.regex' => 'La nazione puo contenere solo testo.',
            'regione.regex' => 'La regione puo contenere solo testo.',
            'provincia.regex' => 'La provincia puo contenere solo testo.',
            'citta.regex' => 'La citta puo contenere solo testo.',
            'indirizzo.regex' => 'L indirizzo puo contenere testo, numeri e segni semplici.',
            'numero_civico.regex' => 'Il numero civico puo contenere testo e numeri.',
            'zona.regex' => 'La zona puo contenere solo testo e numeri semplici.',
            'localita.regex' => 'Il quartiere o la localita puo contenere solo testo e numeri semplici.',
            'cap.regex' => 'Il CAP deve contenere solo numeri.',
            'telefono.regex' => 'Il telefono puo contenere solo numeri e simboli telefonici.',
            'telefono_secondario.regex' => 'Il telefono secondario puo contenere solo numeri e simboli telefonici.',
            'fax.regex' => 'Il fax puo contenere solo numeri e simboli telefonici.',
            'latitudine.required' => 'La latitudine e obbligatoria per identificare correttamente la struttura.',
            'latitudine.numeric' => 'La latitudine deve essere numerica.',
            'longitudine.required' => 'La longitudine e obbligatoria per identificare correttamente la struttura.',
            'longitudine.numeric' => 'La longitudine deve essere numerica.',
            'partita_iva.size' => 'La partita IVA deve avere 11 cifre.',
            'partita_iva.regex' => 'La partita IVA deve contenere solo 11 numeri.',
            'codice_fiscale.regex' => 'Il codice fiscale deve essere di 16 caratteri alfanumerici oppure di 11 cifre numeriche per soggetti diversi da persona fisica.',
            'ragione_sociale.regex' => 'La ragione sociale puo contenere solo testo, numeri e segni semplici di uso comune.',
            'istat_ws_url.url' => 'L URL web service ISTAT deve essere un indirizzo valido.',
            'sito_web.url' => 'Il sito web deve essere un indirizzo valido, ad esempio con .it o .com.',
            'cir.regex' => 'Il CIR puo contenere solo lettere maiuscole, numeri, trattini e barre.',
            'cin.regex' => 'Il CIN puo contenere solo lettere maiuscole, numeri, trattini e barre.',
            'codice_unico.regex' => 'Il codice unico puo contenere solo lettere maiuscole e numeri.',
            'codice_unico.max' => 'Il codice unico non puo superare 7 caratteri.',
        ];
    }
}
