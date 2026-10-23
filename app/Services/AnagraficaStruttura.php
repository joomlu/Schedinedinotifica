<?php

namespace App\Services;

use App\Models\Classificazione;
use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use App\Models\GeoRegione;
use App\Models\Struttura;
use App\Models\TipologiaGenerale;
use App\Models\TipologiaStruttura;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/** Un solo dominio anagrafico; nessuna copia per ruolo e nessun backfill implicito. */
class AnagraficaStruttura
{
    public const OPERATIVI = ['camere_reali_enabled', 'questura_username', 'questura_password', 'questura_wskey'];

    public const MAESTRI = ['nome_struttura', 'tipologia_generale_id', 'tipologia_struttura_id', 'classificazione_id',
        'tipo_apertura', 'data_apertura', 'data_chiusura', 'nazione', 'regione', 'provincia', 'citta', 'localita', 'zona',
        'indirizzo', 'numero_civico', 'cap', 'latitudine', 'longitudine', 'logo', 'logo_citta', 'logo_citta_upload',
        'cir', 'cin', 'ragione_sociale', 'partita_iva', 'codice_fiscale', 'codice_unico',
        'camere_disponibili', 'letti_disponibili', 'letti_agg', 'telefono', 'telefono_secondario', 'email', 'fax', 'sito_web'];

    public const RISERVATI = ['id', 'struttura_id', 'proprietario_id', 'admin_id', 'ruolo', 'ruolo_operativo',
        'attiva', 'attivo', 'piano', 'scadenza_servizio', 'stato_pagamento', 'numero_ricevuta_pagamento',
        'tipologia_generale', 'tipologia_struttura', 'classificazione', 'città', 'località', 'logo_città',
        'istat_username', 'istat_password', 'istat_codice_struttura', 'istat_ws_url', 'questura_codici', 'questura_puk'];

    public function validate(Request $request, ?Struttura $struttura = null, bool $create = false, bool $operational = false): array
    {
        $input = $request->all();
        foreach (['cir', 'cin', 'codice_unico'] as $key) {
            if (array_key_exists($key, $input) && $input[$key] !== null) {
                $input[$key] = mb_strtoupper(preg_replace('/\s+/', '', trim((string) $input[$key])));
            }
        }
        foreach (['nazione' => GeoNazione::class, 'regione' => GeoRegione::class, 'provincia' => GeoProvincia::class, 'citta' => GeoComune::class] as $key => $model) {
            if (isset($input[$key]) && is_numeric($input[$key])) {
                $record = $model::find($input[$key]);
                if (! $record) {
                    throw ValidationException::withMessages([$key => 'Selezione geografica inesistente.']);
                }
                $input[$key] = $key === 'provincia' ? ($record->sigla ?: $record->nome) : $record->nome;
            }
        }
        $required = $create ? 'required' : 'sometimes';
        $rules = [
            'nome_struttura' => [$required, 'filled', 'string', 'max:150'],
            'tipologia_generale_id' => [$required, 'filled', 'integer', 'exists:tipologie_generali,id'],
            'tipologia_struttura_id' => [$required, 'filled', 'integer', 'exists:tipologie_struttura,id'],
            'classificazione_id' => ['sometimes', 'nullable', 'integer', 'exists:classificazioni,id'],
            'conferma_risoluzione_anagrafica' => ['sometimes', 'boolean'],
            'tipo_apertura' => [$required, 'filled', 'in:Annuale,Stagionale'],
            'data_apertura' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'data_chiusura' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'nazione' => [$required, 'filled', 'string', 'max:100'],
            'regione' => [$required, 'filled', 'string', 'max:100'],
            'provincia' => [$required, 'filled', 'string', 'max:100'],
            'citta' => [$required, 'filled', 'string', 'max:150'],
            'indirizzo' => [$required, 'filled', 'string', 'max:191'],
            'cap' => [$required, 'filled', 'string', 'regex:/^[0-9]{4,10}$/'],
            'numero_civico' => ['sometimes', 'nullable', 'string', 'max:20'],
            'zona' => ['sometimes', 'nullable', 'string', 'max:100'],
            'localita' => ['sometimes', 'nullable', 'string', 'max:150'],
            'latitudine' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitudine' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'telefono' => [$required, 'filled', 'string', 'max:20'],
            'telefono_secondario' => ['sometimes', 'nullable', 'string', 'max:20'],
            'fax' => ['sometimes', 'nullable', 'string', 'max:20'],
            'email' => [$required, 'filled', 'email', 'max:191'],
            'sito_web' => ['sometimes', 'nullable', 'url', 'max:191'],
            'cir' => ['sometimes', 'nullable', 'string', 'max:50', 'regex:/^[A-Z0-9\-\/]+$/'],
            'cin' => ['sometimes', 'nullable', 'string', 'max:50', 'regex:/^[A-Z0-9\-\/]+$/'],
            'codice_unico' => ['sometimes', 'nullable', 'string', 'max:7', 'regex:/^[A-Z0-9]+$/'],
            'ragione_sociale' => ['sometimes', 'nullable', 'string', 'max:191'],
            'partita_iva' => ['sometimes', 'nullable', 'string', 'regex:/^[0-9]{11}$/'],
            'codice_fiscale' => ['sometimes', 'nullable', 'string', 'regex:/^(?:[A-Z0-9]{16}|[0-9]{11})$/i'],
            'camere_disponibili' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'letti_disponibili' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'letti_agg' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'logo' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'logo_citta' => $request->hasFile('logo_citta') ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'] : ['sometimes', 'nullable', 'string', 'max:255'],
            'logo_citta_upload' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'camere_reali_enabled' => ['sometimes', 'boolean'],
            'questura_username' => ['sometimes', 'nullable', 'string', 'max:100'],
            'questura_password' => ['sometimes', 'nullable', 'string', 'max:100'],
            'questura_wskey' => ['sometimes', 'nullable', 'string', 'max:191'],
        ];
        if ($operational) {
            $rules = Arr::only($rules, self::OPERATIVI);
        }
        $validator = Validator::make($input, $rules);
        $validator->after(function ($validator) use ($input, $struttura, $operational) {
            if ($operational || $validator->errors()->isNotEmpty()) {
                return;
            }
            if ($struttura?->exists && ! filter_var($input['conferma_risoluzione_anagrafica'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                foreach ($this->display($struttura)['conflitti'] as $campo => $message) {
                    if (array_key_exists($campo, $input) || array_key_exists($campo.'_id', $input)) {
                        $validator->errors()->add('conferma_risoluzione_anagrafica', 'Conferma esplicitamente la risoluzione dei valori discordanti prima di salvare.');
                        break;
                    }
                }
            }
            $ids = $this->selections($struttura ?? new Struttura);
            foreach (['tipologia_generale_id', 'tipologia_struttura_id', 'classificazione_id'] as $key) {
                if (array_key_exists($key, $input)) {
                    $ids[$key] = $input[$key];
                }
            }
            if (array_intersect(array_keys($input), ['tipologia_generale_id', 'tipologia_struttura_id', 'classificazione_id'])) {
                if ($ids['tipologia_struttura_id'] && ! TipologiaStruttura::whereKey($ids['tipologia_struttura_id'])->where('tipologia_generale_id', $ids['tipologia_generale_id'])->exists()) {
                    $validator->errors()->add('tipologia_struttura_id', 'La tipologia struttura non appartiene alla tipologia generale selezionata.');
                }
                if ($ids['classificazione_id'] && (! $ids['tipologia_struttura_id'] || ! Classificazione::whereKey($ids['classificazione_id'])->whereHas('tipologieStruttura', fn ($q) => $q->whereKey($ids['tipologia_struttura_id']))->exists())) {
                    $validator->errors()->add('classificazione_id', 'La classificazione non è compatibile con la tipologia struttura.');
                }
            }
            if (array_intersect(array_keys($input), ['data_apertura', 'data_chiusura'])) {
                $from = $input['data_apertura'] ?? $struttura?->data_apertura?->format('Y-m-d');
                $to = $input['data_chiusura'] ?? $struttura?->data_chiusura?->format('Y-m-d');
                if ($from && $to && $to < $from) {
                    $validator->errors()->add('data_chiusura', 'La chiusura non può precedere l’apertura.');
                }
            }
        });

        return $validator->validate();
    }

    public function payload(array $data, ?Struttura $struttura = null): array
    {
        $payload = Arr::only($data, array_merge(self::MAESTRI, self::OPERATIVI));
        foreach (['tipologia_generale' => TipologiaGenerale::class, 'tipologia_struttura' => TipologiaStruttura::class, 'classificazione' => Classificazione::class] as $text => $model) {
            if (array_key_exists($text.'_id', $payload)) {
                $payload[$text] = $payload[$text.'_id'] ? $model::findOrFail($payload[$text.'_id'])->nome : null;
            }
        }
        foreach (['questura_password', 'questura_wskey'] as $secret) {
            if (blank($payload[$secret] ?? null)) {
                unset($payload[$secret]);
            }
        }
        unset($payload['logo_citta_upload']);
        foreach (['logo', 'logo_citta'] as $key) {
            if (isset($payload[$key]) && ! is_string($payload[$key])) {
                unset($payload[$key]);
            }
        }

        return $payload;
    }

    public function selections(Struttura $s): array
    {
        $ids = [];
        foreach (['tipologia_generale' => TipologiaGenerale::class, 'tipologia_struttura' => TipologiaStruttura::class, 'classificazione' => Classificazione::class] as $text => $model) {
            $key = $text.'_id';
            $ids[$key] = ($s->getAttributes()[$key] ?? null);
            if ($ids[$key] || blank(($s->getAttributes()[$text] ?? null))) {
                continue;
            }
            $query = $model::whereRaw('LOWER(nome) = ?', [mb_strtolower(trim(($s->getAttributes()[$text] ?? null)))]);
            if ($text === 'tipologia_struttura' && $ids['tipologia_generale_id']) {
                $query->where('tipologia_generale_id', $ids['tipologia_generale_id']);
            }
            if ($text === 'classificazione' && $ids['tipologia_struttura_id']) {
                $query->whereHas('tipologieStruttura', fn ($q) => $q->whereKey($ids['tipologia_struttura_id']));
            }
            $matches = $query->pluck('id');
            $ids[$key] = $matches->count() === 1 ? $matches->first() : null;
        }

        return $ids;
    }

    public function display(Struttura $s): array
    {
        $result = [];
        $conflicts = [];
        foreach (['tipologia_generale' => 'tipologiaGenerale', 'tipologia_struttura' => 'tipologiaStruttura', 'classificazione' => 'classificazioneRef'] as $text => $relation) {
            $raw = ($s->getAttributes()[$text] ?? null);
            $ref = $s->{$relation};
            if (($s->getAttributes()[$text.'_id'] ?? null) && (! $ref || (filled($raw) && mb_strtolower(trim($raw)) !== mb_strtolower(trim($ref->nome))))) {
                $conflicts[$text] = 'Conflitto tra valore storico e catalogo: verificare esplicitamente la selezione.';
            }
            $result[$text] = $ref?->nome ?? $raw;
        }

        $ids = $this->selections($s);
        if ($ids['tipologia_struttura_id'] && $ids['tipologia_generale_id']
            && ! TipologiaStruttura::whereKey($ids['tipologia_struttura_id'])->where('tipologia_generale_id', $ids['tipologia_generale_id'])->exists()) {
            $conflicts['tipologia_struttura'] = 'Tipologia struttura e generale non compatibili.';
        }
        if ($ids['classificazione_id'] && $ids['tipologia_struttura_id']
            && ! Classificazione::whereKey($ids['classificazione_id'])->whereHas('tipologieStruttura', fn ($q) => $q->whereKey($ids['tipologia_struttura_id']))->exists()) {
            $conflicts['classificazione'] = 'Classificazione non compatibile con la tipologia struttura.';
        }
        foreach (['citta' => 'città', 'localita' => 'località', 'logo_citta' => 'logo_città'] as $plain => $accent) {
            $attributes = $s->getAttributes();
            if (filled($attributes[$plain] ?? null) && filled($attributes[$accent] ?? null) && $attributes[$plain] !== $attributes[$accent]) {
                $conflicts[$plain] = 'Valori discordanti nelle colonne legacy: verifica amministrativa necessaria.';
            }
        }
        return ['valori' => $result, 'conflitti' => $conflicts];
    }

    public function fiscal(Struttura $s): array
    {
        $display = $this->display($s);
        $fiscalConflicts = Arr::only($display['conflitti'], ['tipologia_generale', 'tipologia_struttura', 'classificazione', 'citta']);
        if ($fiscalConflicts) {
            throw ValidationException::withMessages($fiscalConflicts + ['categoria_tassa_non_certificata' => 'Dati struttura discordanti: verificare tipologia e classificazione prima del calcolo.']);
        }

        return $display['valori'];
    }

    public function formData(Struttura $s): array
    {
        return ['tipologieGenerali' => TipologiaGenerale::all(), 'tipologieStruttura' => TipologiaStruttura::with('generale')->get(),
            'classificazioni' => Classificazione::with('tipologieStruttura')->get(), 'selezioniTipologia' => $this->selections($s),
            'anagraficaConflitti' => $this->display($s)['conflitti']];
    }
}
