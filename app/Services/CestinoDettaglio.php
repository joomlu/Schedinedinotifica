<?php

namespace App\Services;

use App\Models\CestinoItem;
use Carbon\Carbon;

final class CestinoDettaglio
{
    private const LABELS = [
        'name' => 'Nome', 'nome' => 'Nome', 'surname' => 'Cognome', 'cognome' => 'Cognome',
        'nome_struttura' => 'Struttura', 'nome_referente' => 'Referente', 'titolo' => 'Titolo',
        'descrizione' => 'Descrizione', 'note' => 'Note', 'email' => 'Email', 'telefono' => 'Telefono',
        'telefono_secondario' => 'Cellulare', 'cellulare' => 'Cellulare', 'whatsapp' => 'WhatsApp',
        'numero_prenotazione' => 'Numero prenotazione', 'codice' => 'Codice', 'scheda' => 'Numero schedina',
        'arrivo' => 'Arrivo', 'arrive' => 'Arrivo', 'partenza' => 'Partenza', 'departure' => 'Partenza',
        'data_evento' => 'Data', 'ora_evento' => 'Ora', 'data_apertura' => 'Apertura', 'data_chiusura' => 'Chiusura',
        'data_inizio' => 'Inizio', 'data_scadenza' => 'Scadenza', 'date_nac' => 'Data di nascita', 'oa_date_nac' => 'Data di nascita',
        'nazione' => 'Nazione', 'country' => 'Nazione', 'regione' => 'Regione', 'provincia' => 'Provincia',
        'citta' => 'Città', 'città' => 'Città', 'city' => 'Città', 'indirizzo' => 'Indirizzo',
        'address' => 'Indirizzo', 'numero_civico' => 'Numero civico', 'cap' => 'CAP', 'zona' => 'Zona', 'localita' => 'Località',
        'ragione_sociale' => 'Ragione sociale', 'partita_iva' => 'Partita IVA', 'codice_fiscale' => 'Codice fiscale',
        'cir' => 'CIR', 'cin' => 'CIN', 'tipologia_generale' => 'Tipologia generale', 'tipologia_struttura' => 'Tipologia struttura',
        'classificazione' => 'Classificazione', 'tipo_apertura' => 'Tipo apertura', 'sito_web' => 'Sito web',
        'stato' => 'Stato', 'tipo' => 'Tipo', 'circuito' => 'Circuito', 'ruolo' => 'Ruolo',
        'cant_people' => 'Numero ospiti', 'quantita_persone' => 'Numero ospiti', 'numero_adulti' => 'Adulti',
        'numero_bambini' => 'Bambini', 'room' => 'Camera', 'beds' => 'Letti', 'camere_disponibili' => 'Camere disponibili',
        'letti_disponibili' => 'Letti disponibili', 'letti_agg' => 'Letti aggiuntivi',
        'observation' => 'Note soggiorno', 'customer_observation' => 'Note ospite',
        'customer_email' => 'Email ospite', 'customer_phone' => 'Telefono ospite',
        'quantita' => 'Quantità', 'prezzo' => 'Prezzo', 'numero_licenza' => 'Numero licenza',
        'schedina' => 'Soggiorno', 'componenti' => 'Ospiti', 'camere' => 'Camere',
    ];

    public function rows(CestinoItem $item): array
    {
        return $this->flatten(app(CestinoService::class)->displayPayload($item));
    }

    private function flatten(array $payload, string $prefix = ''): array
    {
        $rows = [];
        foreach ($payload as $key => $value) {
            $label = is_int($key) ? 'Voce '.($key + 1) : (self::LABELS[$key] ?? null);
            if ($label === null || $value === null || $value === '') {
                continue;
            }
            $label = $prefix ? $prefix.' · '.$label : $label;
            if (is_array($value)) {
                $rows = array_merge($rows, $this->flatten($value, $label));
                continue;
            }
            if (!is_scalar($value)) {
                continue;
            }
            if (is_bool($value)) {
                $value = $value ? 'Sì' : 'No';
            } elseif (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T].*)?$/D', $value)) {
                try {
                    $dateOnly = in_array($key, ['arrivo', 'arrive', 'partenza', 'departure', 'data_evento', 'data_apertura', 'data_chiusura', 'data_inizio', 'data_scadenza', 'date_nac', 'oa_date_nac'], true);
                    $value = Carbon::parse($value)->timezone(config('app.timezone'))->format($dateOnly || strlen($value) === 10 ? 'd/m/Y' : 'd/m/Y H:i');
                } catch (\Throwable) {
                    // I valori storici non validi restano testo, senza interrompere il dettaglio.
                }
            } elseif (in_array($key, ['stato', 'tipo', 'circuito', 'ruolo'], true)) {
                $value = match ((string) $value) {
                    'super_admin' => 'Superadmin', 'struttura_user' => 'Utente struttura',
                    'web', 'web_checkin' => 'Web Check-in', 'da_fare' => 'Da fare',
                    default => ucfirst(str_replace('_', ' ', (string) $value)),
                };
            }
            $rows[] = ['etichetta' => $label, 'valore' => (string) $value];
        }

        return $rows;
    }
}
