<?php

namespace App\Services;

use App\Models\GeoNazione;
use App\Models\TipoDocumento;
use App\Support\Componenti\TipoAlloggiatoCatalogo;
use Illuminate\Validation\ValidationException;

final class AccessDatiSchedina
{
    public function converti(array $source): array
    {
        $p = $source['prenotazione']; $t = $source['titolare']; $g = $source['gruppo'];
        $warnings = ['Verificare e completare i dati prima del salvataggio definitivo. Nessun consenso privacy trasferito da Access.',
            'Confermare le classificazioni ISTAT ed eventuali esenzioni nel gestionale.'];
        $role = $this->ruolo($g['tipo_alloggiato'], false);
        if (($g['tipo_alloggiato'] === '16') !== (count($g['componenti']) === 0)) {
            throw ValidationException::withMessages(['gruppo' => 'Tipo del titolare incoerente con gli accompagnatori.']);
        }
        if ((int) $p['numero_ospiti'] !== 1 + count($g['componenti'])) {
            throw ValidationException::withMessages(['prenotazione.numero_ospiti' => 'Il totale non coincide con titolare e componenti.']);
        }
        $d = $t['dati'];
        $data = [
            'name' => $d['nome'], 'surname' => $d['cognome'], 'sex' => $d['sesso'],
            'relationship' => $role, 'customer_email' => $p['email_ospite'] ?? null,
            'customer_cellphone' => $p['telefono_ospite'] ?? null,
            'arrive' => $g['data_arrivo'], 'departure' => $g['data_partenza'],
            'cant_people' => $p['numero_ospiti'], 'room' => $p['camere_occupate'] ?? null, 'beds' => $p['letti_occupati'] ?? null,
            'oa_date_nac' => $this->data($d['data_nascita'] ?? null),
            'oa_city' => $d['luogo_nascita'] ?? null,
            'oa_city_nac' => $this->nazione($d['cittadinanza'] ?? null, true, $warnings),
            'or_country' => $this->nazione($d['paese_residenza'] ?? null, false, $warnings),
            'or_city' => $d['comune_residenza'] ?? null,
            'or_prov' => $d['sigla_provincia_residenza'] ?? null, 'or_region' => $d['regione_residenza'] ?? null,
            'or_cap' => $d['cap_residenza'] ?? null, 'or_address' => $d['indirizzo_residenza'] ?? null,
            'or_num' => $d['numero_civico'] ?? null, 'or_doc' => $d['numero_documento'] ?? null,
            'or_doctype' => $this->documento($t['tipo_documento'], $warnings),
            'or_published_date' => $this->data($d['data_rilascio'] ?? null), 'or_expire' => $this->data($d['data_scadenza'] ?? null),
            'or_published' => $d['autorita_rilascio'] ?? null, 'or_published_city' => $d['luogo_rilascio'] ?? null,
            'or_published_country' => $this->nazione($d['stato_emittente'] ?? null, false, $warnings),
            'fonte_prenotazione' => 'tanggo_access', 'id_prenotazione_esterna' => $source['richiesta_id'],
            'customer_privacy_consent' => false,
        ];
        $warnings[] = 'Completare o verificare Stato e comune di nascita del titolare: Access fornisce il luogo in testo libero.';
        $rows = [];
        foreach ($g['componenti'] as $c) {
            $d = $c['dati'];
            $expected = $g['tipo_alloggiato'] === '17' ? '19' : '20';
            if ($c['tipo_alloggiato'] !== $expected) throw ValidationException::withMessages(['gruppo.componenti' => 'Tipo componente incoerente con il capofamiglia/capogruppo.']);
            $rows[] = [
                'name' => $d['nome'], 'surname' => $d['cognome'], 'sex' => $d['sesso'],
                'relationship' => $this->ruolo($c['tipo_alloggiato'], true),
                'date_nac' => $this->data($d['data_nascita'] ?? null),
                'city_nac' => $this->nazione($d['cittadinanza'] ?? null, true, $warnings),
                'country_nac' => $this->nazione($d['stato_nascita'] ?? null, false, $warnings),
                'province_nac' => $d['provincia_nascita'] ?? null, 'comune_nac' => $d['comune_nascita'] ?? null,
                'country' => $this->nazione($d['paese_residenza'] ?? null, false, $warnings), 'city' => $d['comune_residenza'] ?? null,
            ];
        }
        $extra = [];
        foreach (['codice_fiscale'=>'Codice fiscale','statura_cm'=>'Statura (cm)','colore_occhi'=>'Colore occhi','categorie_patente'=>'Categorie patente'] as $key=>$label) {
            if (!empty($source['titolare']['dati'][$key])) $extra[] = $label.': '.$source['titolare']['dati'][$key];
        }
        $data['componenti'] = $rows;
        if ($data['arrive'] >= $data['departure']) $warnings[] = 'Il soggiorno in giornata richiede revisione: la schedina definitiva richiede partenza successiva all’arrivo.';
        if (($source['origine'] ?? 'checkin') === 'documento') $warnings[] = 'Bozza da acquisizione documentale: identità, check-in e ingresso non confermati.';
        if ($data['or_expire'] && $data['or_expire'] < now()->toDateString()) $warnings[] = 'Documento scaduto: da revisionare prima della conferma.';
        $data['observation'] = 'Access '.$source['codice_checkin'].' · Prenotazione '.($p['riferimento'] ?? '')."\n".implode("\n", array_unique($warnings)).($extra ? "\nUlteriori dati dal documento: ".implode("; ", $extra) : '');
        return [$data, array_values(array_unique($warnings))];
    }

    private function ruolo(string $code, bool $component): string
    {
        foreach ($component ? TipoAlloggiatoCatalogo::opzioniComponente() : TipoAlloggiatoCatalogo::opzioniCapoSchedina() as $row) {
            if ($row['codice'] === $code) return $row['descrizione'];
        }
        throw ValidationException::withMessages(['gruppo.tipo_alloggiato' => 'Tipo alloggiato assente dal catalogo.']);
    }

    private function data(?string $value): ?string
    {
        if (!$value) return null;
        foreach (['Y-m-d', 'd/m/Y', 'd.m.Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!'.$format, $value);
            if ($date && $date->format($format) === $value) return $date->format('Y-m-d');
        }
        throw ValidationException::withMessages(['titolare' => 'Data del documento non valida.']);
    }

    private function nazione(?string $value, bool $citizenship, array &$warnings): ?string
    {
        if (!$value) return null;
        $rows = GeoNazione::query()->where(function ($q) use ($value) {
            $q->where('nome', $value)->orWhere('cittadinanza', $value)->orWhere('codice_iso2', $value);
        })->get();
        if ($rows->count() === 1) return $citizenship ? ($rows[0]->cittadinanza ?: $rows[0]->nome) : $rows[0]->nome;
        $warnings[] = 'Riferimento geografico da riconciliare: '.$value;
        // Il testo resta visibile, ma non viene mai interpretato come ID di un altro archivio.
        return $value;
    }

    private function documento(string $type, array &$warnings): ?string
    {
        $descriptions = match ($type) {
            'carta_identita_elettronica' => ['CARTA DI IDENTITA ELETTRONICA', 'CARTA DI IDENTITA'],
            'passaporto' => ['PASSAPORTO ORDINARIO', 'PASSAPORTO'],
            'patente_guida' => ['PATENTE DI GUIDA'],
            default => [],
        };
        $normalize = fn ($v) => preg_replace('/[^A-Z0-9]/', '', strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $v)));
        foreach ($descriptions as $description) {
            $matches = TipoDocumento::all()->filter(fn ($row) => $normalize($row->descrizione) === $normalize($description));
            if ($matches->count() === 1) return $matches->first()->descrizione;
        }
        $warnings[] = 'Tipo documento da selezionare: '.$type;
        return null;
    }
}
