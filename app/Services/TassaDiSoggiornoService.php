<?php

namespace App\Services;

use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Support\Anagrafica\EtaOperativa;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TassaDiSoggiornoService
{
    public function regolaAlbergoBellaria(Struttura $struttura): ?array
    {
        $tipo = mb_strtolower(trim((string) ($struttura->tipologia_struttura ?: $struttura->tipologiaStruttura?->nome)));
        $classe = mb_strtolower(trim((string) ($struttura->classificazione ?: $struttura->classificazioneRef?->nome)));
        if (! in_array($tipo, ['albergo', 'alberghi', 'hotel'], true)
            || ! preg_match('/^([1-5])\s*stell[ae](?:\s+superior)?$/u', $classe, $match)) {
            return null;
        }

        return ['tassa_soggiorno' => [1 => '1.00', 2 => '1.00', 3 => '1.50', 4 => '2.50', 5 => '2.50'][(int) $match[1]], 'giorni_massimo' => 6];
    }

    public function validaCategoriaBellaria(?Struttura $struttura): ?array
    {
        if (! in_array(mb_strtolower(trim((string) ($struttura?->citta ?? ''))), ['bellaria-igea marina', 'bellaria igea marina'], true)) {
            return null;
        }

        $regola = $this->regolaAlbergoBellaria($struttura);
        if (! $regola) {
            throw \Illuminate\Validation\ValidationException::withMessages(['categoria_tassa_non_certificata' => 'Categoria della struttura non ancora configurata/certificata per l’Imposta di Soggiorno del Comune di Bellaria Igea Marina. Calcolo Tassa non disponibile.']);
        }

        return $regola;
    }

    public function validaConfigurazioneBellaria(?Struttura $struttura, ?TassaDiSoggiorno $config, int|Carbon|null $data): void
    {
        if (! $this->validaCategoriaBellaria($struttura)) {
            return;
        }
        $regola = $this->risolviProfiloFiscale($struttura, $data instanceof Carbon ? $data : Carbon::create($data ?? now()->year, 1, 1));
        if (! $config || $config->tassa_soggiorno === null || $config->giorni_massimo === null) {
            throw \Illuminate\Validation\ValidationException::withMessages(['configurazione_tassa' => 'Configurare aliquota e limite prima di elaborare i movimenti Tassa.']);
        }
        if ((float) $config->tassa_soggiorno !== (float) $regola['tassa_soggiorno'] || (int) $config->giorni_massimo !== $regola['giorni_massimo']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['categoria_tassa' => 'Aliquota o limite non coerenti con il profilo fiscale certificato per la data del soggiorno. Verificare la configurazione senza riscrivere lo storico.']);
        }
        $inizio = $this->parseDate($config->inizio ? (string) $config->inizio : null);
        $fine = $this->parseDate($config->fine ? (string) $config->fine : null);
        if (! $inizio || ! $fine || $inizio->format('m-d') !== Carbon::parse($regola['inizio'])->format('m-d') || $fine->format('m-d') !== Carbon::parse($regola['fine'])->format('m-d')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['periodo_tassa' => 'La configurazione Bellaria deve indicare il periodo documentato 1 giugno–30 settembre. Le impostazioni esistenti non sono state riscritte.']);
        }
    }

    public function datiStrutturaFiscali(Struttura $struttura): array
    {
        return [
            'comune' => trim((string) $struttura->citta),
            'tipologia_generale' => trim((string) ($struttura->tipologia_generale ?: $struttura->tipologiaGenerale?->nome)),
            'tipologia_struttura' => trim((string) ($struttura->tipologia_struttura ?: $struttura->tipologiaStruttura?->nome)),
            'classificazione' => trim((string) ($struttura->classificazione ?: $struttura->classificazioneRef?->nome)),
        ];
    }

    protected function profiliFiscaliBellaria(): array
    {
        return [[
            'valida_dal' => '2026-01-01', 'valida_al' => '2026-12-31',
            'regola_versione' => 'bellaria-alberghi-2026-v1',
            'regola_fonte' => 'GC184/2025, regolamento CC29/2026 e specifica importazione StayTour Bellaria',
            'tariffe' => [1 => '1.00', 2 => '1.00', 3 => '1.50', 4 => '2.50', 5 => '2.50'],
            'giorni_massimo' => 6, 'inizio' => '2026-06-01', 'fine' => '2026-09-30',
            'max_age_children' => 17, 'min_age_adult' => 18,
        ]];
    }

    public function profiloFiscaleBellaria(Struttura $struttura, int $anno): ?array
    {
        return $this->risolviProfiloFiscale($struttura, Carbon::create($anno, 1, 1));
    }

    public function risolviProfiloFiscale(Struttura $struttura, Carbon $data): ?array
    {
        $dati = $this->datiStrutturaFiscali($struttura);
        if ($dati['comune'] === '') {
            throw \Illuminate\Validation\ValidationException::withMessages(['dato_struttura_mancante' => 'Comune non configurato nei Dati struttura.']);
        }
        if (! in_array(mb_strtolower($dati['comune']), ['bellaria-igea marina', 'bellaria igea marina'], true)) {
            return null;
        }
        foreach (['tipologia_generale' => 'Tipologia generale', 'tipologia_struttura' => 'Tipologia struttura', 'classificazione' => 'Classificazione della struttura'] as $campo => $etichetta) {
            if ($dati[$campo] === '') {
                $messaggio = $etichetta.' non configurata nei Dati struttura.';
                throw \Illuminate\Validation\ValidationException::withMessages(['dato_struttura_mancante' => $messaggio, 'categoria_tassa_non_certificata' => $messaggio]);
            }
        }
        if (mb_strtolower($dati['tipologia_generale']) !== 'alberghiera') {
            throw \Illuminate\Validation\ValidationException::withMessages(['categoria_tassa_non_certificata' => 'Questa tipologia generale non è ancora certificata per l’Imposta di Soggiorno di Bellaria-Igea Marina.']);
        }
        $this->validaCategoriaBellaria($struttura);
        $profili = collect($this->profiliFiscaliBellaria())->filter(fn ($profilo) => $data->copy()->startOfDay()->betweenIncluded(Carbon::parse($profilo['valida_dal'])->startOfDay(), Carbon::parse($profilo['valida_al'])->endOfDay()));
        if ($profili->count() !== 1) {
            throw \Illuminate\Validation\ValidationException::withMessages(['regola_non_disponibile' => 'Non è disponibile una configurazione fiscale certificata univoca per la data del soggiorno '.$data->format('d/m/Y').'. Calcolo Tassa non disponibile.']);
        }
        $profilo = $profili->first();
        preg_match('/^([1-5])/u', $dati['classificazione'], $classe);
        $profilo['tassa_soggiorno'] = $profilo['tariffe'][(int) $classe[1]];
        unset($profilo['tariffe']);

        return array_merge($profilo, ['regola_categoria' => $dati['classificazione']]);
    }

    public const CAMPI_FISCALI = ['tassa_soggiorno', 'giorni_massimo', 'inizio', 'fine', 'max_age_children', 'min_age_adult'];

    public function valoriConfigurazione(?TassaDiSoggiorno $config): array
    {
        $valori = [];
        foreach (self::CAMPI_FISCALI as $campo) {
            $valore = $config?->{$campo};
            $valori[$campo] = $valore === null ? null : match ($campo) {
                'inizio', 'fine' => Carbon::parse($valore)->toDateString(),
                'tassa_soggiorno' => number_format((float) $valore, 2, '.', ''),
                default => (int) $valore,
            };
        }

        return $valori;
    }

    public function confrontoConfigurazione(Struttura $struttura, ?TassaDiSoggiorno $config, int $anno): array
    {
        $profilo = $this->profiloFiscaleBellaria($struttura, $anno);
        if (! $profilo) {
            throw \Illuminate\Validation\ValidationException::withMessages(['configurazione_tassa' => 'Riallineamento disponibile soltanto per Bellaria.']);
        }
        $attuali = $this->valoriConfigurazione($config);
        $richiesti = $this->valoriConfigurazione((new TassaDiSoggiorno)->forceFill($profilo));
        $differenze = [];
        foreach ($richiesti as $campo => $valore) {
            if ($attuali[$campo] !== $valore) {
                $differenze[$campo] = ['attuale' => $attuali[$campo], 'richiesto' => $valore];
            }
        }

        return compact('profilo', 'attuali', 'richiesti', 'differenze');
    }

    public function improntaConfigurazione(Struttura $struttura, ?TassaDiSoggiorno $config, int $anno): string
    {
        return hash('sha256', json_encode([$config?->id, $config?->getRawOriginal(), $this->datiStrutturaFiscali($struttura), $this->confrontoConfigurazione($struttura, $config, $anno)], JSON_THROW_ON_ERROR));
    }

    public function configurazioneAutomatica(Struttura $struttura, ?TassaDiSoggiorno $legacy, int|Carbon $data): ?TassaDiSoggiorno
    {
        $data = is_int($data) ? Carbon::create($data, 1, 1) : $data;
        $profilo = $this->risolviProfiloFiscale($struttura, $data);
        if (! $profilo) {
            return $legacy;
        }
        // I record discordanti restano bloccati. Nessun riallineamento silenzioso o salvataggio.
        $campi = ['tassa_soggiorno', 'giorni_massimo', 'inizio', 'fine', 'max_age_children', 'min_age_adult'];
        $haValori = $legacy && collect($campi)->contains(fn ($campo) => $legacy->{$campo} !== null);
        if ($haValori) {
            try {
                $this->validaConfigurazioneBellaria($struttura, $legacy, $data);
                if (($legacy->max_age_children !== null && $legacy->max_age_children !== 17) || ($legacy->min_age_adult !== null && $legacy->min_age_adult !== 18)) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['configurazione_tassa' => 'Parametri età legacy discordanti.']);
                }
            } catch (\Illuminate\Validation\ValidationException $exception) {
                throw \Illuminate\Validation\ValidationException::withMessages(['configurazione_legacy_discordante' => 'Struttura ID '.$struttura->id.' — anno '.$data->year.': '.collect($this->confrontoConfigurazione($struttura, $legacy, $data->year)['differenze'])->map(fn ($d, $campo) => $campo.': attuale '.($d['attuale'] ?? 'mancante').', richiesto '.$d['richiesto'])->implode('; ').'. Aprire Tassa di soggiorno → Configurazione e confermare il riallineamento. Record conservato, calcolo non disponibile.']);
            }
        }
        $effettiva = $legacy ? clone $legacy : new TassaDiSoggiorno(['struttura_id' => $struttura->id]);
        $effettiva->forceFill($profilo);

        return $effettiva;
    }

    public function diagnosiConfigurazione(Struttura $struttura, ?TassaDiSoggiorno $legacy, int $anno): array
    {
        $profilo = null;
        try {
            $profilo = $this->profiloFiscaleBellaria($struttura, $anno);
            $effettiva = $this->configurazioneAutomatica($struttura, $legacy, $anno);

            return ['stato' => $profilo ? 'automatica' : 'manuale_altro_comune', 'messaggio' => $profilo ? 'Configurazione fiscale automatica' : 'Configurazione del Comune corrente', 'profilo' => $profilo, 'configurazione' => $effettiva];
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $errori = $exception->errors();

            return ['stato' => array_key_first($errori), 'messaggio' => reset($errori)[0], 'profilo' => $profilo, 'configurazione' => null];
        }
    }

    public function catalogoBellaria(Struttura $struttura, Collection $esistenti): Collection
    {
        if (! in_array(mb_strtolower(trim((string) $struttura->citta)), ['bellaria-igea marina', 'bellaria igea marina'], true)) {
            return $esistenti;
        }
        $ufficiali = [
            '400' => 'Minori fino al compimento di 18 anni', '405' => 'Soggetti in terapia e accompagnatori',
            '410' => 'Soggetti invalidi e accompagnatore', '415' => 'Volontari in eventi organizzati o di emergenza',
            '420' => 'Soggetti in eventi calamitosi o di emergenza', '425' => 'Autisti di pullman e accompagnatori turistici',
            '430' => 'Personale dipendente della struttura', '440' => 'Forze Armate e Vigili del Fuoco in servizio',
        ];
        $catalogo = collect($ufficiali)->map(fn ($descrizione, $codice) => new TassaEsenzione(['struttura_id' => $struttura->id, 'codice' => (string) $codice, 'descrizione' => $descrizione, 'attivo' => true, 'richiede_nota' => in_array((string) $codice, ['405', '410', '415', '420', '440'], true)]));

        return $catalogo->values()->concat($esistenti->filter(fn ($voce) => ! array_key_exists((string) $voce->codice, $ufficiali) && (string) $voce->codice !== '777'))->values();
    }

    public function parseDate(?string $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', Carbon::ISO8601] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function diffNotti(?Carbon $arrivo, ?Carbon $partenza): int
    {
        if (! $arrivo || ! $partenza) {
            return 0;
        }

        return max(0, $arrivo->diffInDays($partenza));
    }

    public function diffNottiNelPeriodo(?Carbon $arrivo, ?Carbon $partenza, ?TassaDiSoggiorno $config): int
    {
        if (! $arrivo || ! $partenza) {
            return 0;
        }

        $inizioRaw = $config?->inizio;
        $fineRaw = $config?->fine;

        $inizio = $inizioRaw instanceof Carbon ? $inizioRaw->copy() : $this->parseDate($inizioRaw ? (string) $inizioRaw : null);
        $fine = $fineRaw instanceof Carbon ? $fineRaw->copy() : $this->parseDate($fineRaw ? (string) $fineRaw : null);

        if (! $inizio || ! $fine) {
            return $this->diffNotti($arrivo, $partenza);
        }

        $count = 0;
        $cursor = $arrivo->copy()->startOfDay();
        $end = $partenza->copy()->startOfDay();

        while ($cursor->lt($end)) {
            if ($this->isDataNelPeriodo($cursor, $inizio, $fine)) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }

    public function dettaglioSchedina(Schedina $schedina, Collection $componenti, ?TassaDiSoggiorno $config, Collection $esenzioni, ?Struttura $struttura = null): array
    {
        $arrivo = $this->parseDate($schedina->arrive);
        $partenza = $this->parseDate($schedina->departure);
        $giorniMax = $config && $config->giorni_massimo !== null ? (int) $config->giorni_massimo : null;
        $aliquota = $config ? (float) str_replace(',', '.', $config->tassa_soggiorno ?? 0) : 0.0;

        $righe = [];
        $totale = 0;
        $oltreMaxTotale = 0;

        $bellaria = in_array(mb_strtolower(trim((string) ($struttura?->citta ?? ''))), ['bellaria-igea marina', 'bellaria igea marina'], true);
        $this->validaConfigurazioneBellaria($struttura, $config, $arrivo);
        if ($bellaria && (! $arrivo || ! $partenza || $partenza->lessThan($arrivo))) {
            throw \Illuminate\Validation\ValidationException::withMessages(['date_soggiorno' => 'Date del soggiorno mancanti o incoerenti. Verificare la Schedina prima del calcolo fiscale.']);
        }
        $profiliNotti = [];
        if ($bellaria && $arrivo && $partenza && $partenza->greaterThan($arrivo)) {
            for ($notte = $arrivo->copy()->startOfDay(); $notte->lessThan($partenza->copy()->startOfDay()); $notte->addDay()) {
                $pertinente = $this->risolviProfiloFiscale($struttura, $notte);
                // Un limite diverso richiede una regola di transizione documentata.
                // Non si resetta il contatore né si inventa un limite comune.
                if ((int) $pertinente['giorni_massimo'] !== $giorniMax) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['regola_non_disponibile' => 'Limiti differenti durante il soggiorno: manca una regola certificata di transizione.']);
                }
                $profiliNotti[$notte->toDateString()] = $pertinente;
            }
        }
        $persone = collect();
        $persone->push(['persona_id' => $schedina->id, 'persona_tipo' => 'schedina', 'nome' => trim(($schedina->surname ? $schedina->surname.' ' : '').($schedina->name ?? '')), 'exent' => $schedina->exent, 'nascita' => $schedina->oa_date_nac, 'eta' => $bellaria ? null : $this->etaFromString($schedina->oa_date_nac ?? null)]);
        foreach ($componenti as $comp) {
            $persone->push([
                'persona_id' => $comp->id,
                'persona_tipo' => 'componente',
                'nome' => trim(($comp->surname ? $comp->surname.' ' : '').($comp->name ?? '')),
                'exent' => $comp->exent,
                'nascita' => $comp->date_nac,
                'eta' => $bellaria ? null : $this->etaFromString($comp->date_nac ?? null),
            ]);
        }

        foreach ($persone as $persona) {
            $info = $bellaria
                ? $this->calcolaPersonaBellaria($persona, $arrivo, $partenza, $giorniMax, $aliquota, $config, $esenzioni, $profiliNotti)
                : $this->calcolaPersona($persona, $arrivo, $partenza, $giorniMax, $aliquota, $config, $esenzioni);
            $righe[] = $info;
            $totale += $info['subtotale'];
            $oltreMaxTotale += $info['notti_oltre_max'];
        }

        return [
            'righe' => $righe,
            'totale' => $totale,
            'notti_oltre_max' => $oltreMaxTotale,
            'notti_totali' => $this->diffNotti($arrivo, $partenza),
            'bellaria' => $bellaria,
            'profili_fiscali' => array_values(collect($profiliNotti)->unique('regola_versione')->all()),
        ];
    }

    public function exportRows(array $dettaglio, ?Carbon $arrivo, ?Carbon $partenza): array
    {
        $rows = [];
        foreach ($dettaglio['righe'] as $persona) {
            foreach ($persona['segmenti'] ?? [$persona] as $riga) {
                $rows[] = [
                    'tipo' => $riga['codice'] ?? 0,
                    'data_reg' => ! empty($dettaglio['bellaria']) ? $arrivo?->toDateString() : now()->toDateString(),
                    'arrivo' => $arrivo?->toDateString(),
                    'partenza' => $partenza?->toDateString(),
                    'nominativo' => $riga['nome'],
                    'soggetti' => 1,
                    'pernottamenti' => $riga['notti_imponibili'],
                    'tariffa' => $riga['aliquota'],
                ];
                if ($riga['notti_oltre_max'] > 0) {
                    $rows[] = [
                        'tipo' => 777,
                        'data_reg' => ! empty($dettaglio['bellaria']) ? $arrivo?->toDateString() : now()->toDateString(),
                        'arrivo' => $arrivo?->toDateString(),
                        'partenza' => $partenza?->toDateString(),
                        'nominativo' => $riga['nome'],
                        'soggetti' => 1,
                        'pernottamenti' => $riga['notti_oltre_max'],
                        'tariffa' => 0,
                    ];
                }
            }

        }

        return $rows;
    }

    private function calcolaPersonaBellaria(array $persona, ?Carbon $arrivo, ?Carbon $partenza, ?int $giorniMax, float $aliquota, ?TassaDiSoggiorno $config, Collection $esenzioni, array $profiliNotti): array
    {
        // Tariffa/periodo pertinenti a ciascuna notte; contatore unico per il soggiorno.
        $nascita = $this->parseDate($persona['nascita'] ?? null)?->startOfDay();
        if ($nascita && $arrivo && $nascita->gt($arrivo)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['nascita' => 'Data di nascita successiva all’arrivo: verificare prima del calcolo Tassa.']);
        }
        $persona['eta'] = $nascita && $arrivo && $nascita->lte($arrivo)
            ? (int) $nascita->diffInYears($arrivo->copy()->startOfDay()) : null;
        $base = $this->calcolaPersona($persona, $arrivo, $partenza, $giorniMax, $aliquota, $config, $esenzioni);
        $esenzione = $this->resolveEsenzione($persona['exent'] ?? null, $esenzioni);
        // Il codice 400 è una condizione d'età, non un'esenzione permanente dopo il compleanno.
        if ($nascita && (string) $esenzione?->codice === '400') {
            $esenzione = null;
        }
        if (! $esenzione && filled($persona['exent'] ?? null) && ! in_array(strtoupper(trim($persona['exent'])), ['NO', '400', '777'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['exent' => 'Esenzione legacy non riconosciuta: scegliere un motivo documentato prima di elaborare la Tassa.']);
        }
        $compleanno = $nascita?->copy()->addYears(18);
        $segmenti = [];
        $considerate = 0;
        if ($arrivo && $partenza) {
            for ($giorno = $arrivo->copy()->startOfDay(); $giorno->lt($partenza->copy()->startOfDay()); $giorno->addDay()) {
                $profiloNotte = $profiliNotti[$giorno->toDateString()];
                $tariffaNotte = (float) $profiloNotte['tassa_soggiorno'];
                if (! $giorno->betweenIncluded(Carbon::parse($profiloNotte['inizio'])->startOfDay(), Carbon::parse($profiloNotte['fine'])->endOfDay())) {
                    continue;
                }
                $considerate++;
                if ($giorniMax !== null && $considerate > $giorniMax) {
                    continue;
                }
                $motivo = $esenzione;
                if (! $motivo && $compleanno && $giorno->lte($compleanno)) {
                    $motivo = $this->resolveAutomaticEsenzione($esenzioni, ['400'], ['minori'], '400', 'Minori fino al compimento di 18 anni');
                }
                $codice = $motivo?->codice ?? 0;
                $key = (string) $codice.'|'.$tariffaNotte.'|'.$profiloNotte['regola_versione'];
                if (! isset($segmenti[$key])) {
                    $segmenti[$key] = array_merge($base, [
                        'codice' => $codice, 'esente' => $motivo !== null, 'motivo' => $motivo?->descrizione,
                        'notti_imponibili' => 0, 'notti_tassate' => 0, 'notti_oltre_max' => 0,
                        'aliquota' => $motivo ? 0.0 : $tariffaNotte, 'subtotale' => 0.0,
                        'regola_versione' => $profiloNotte['regola_versione'],
                    ]);
                }
                $segmenti[$key]['notti_imponibili']++;
                if (! $motivo) {
                    $segmenti[$key]['notti_tassate']++;
                    $segmenti[$key]['subtotale'] += $tariffaNotte;
                }
            }
        }
        $base['notti_periodo'] = $considerate;
        $base['notti_imponibili'] = $giorniMax === null ? $considerate : min($considerate, $giorniMax);
        $base['notti_oltre_max'] = $giorniMax === null ? 0 : max(0, $considerate - $giorniMax);
        if (! $segmenti) {
            $base['notti_tassate'] = 0;
            $base['notti_esenti'] = 0;
            $base['subtotale'] = 0.0;
            $base['motivo'] = $base['notti_totali'] === 0
                ? 'Soggiorno senza pernottamenti imponibili'
                : ($considerate === 0 ? 'Soggiorno fuori dal periodo di applicazione dell’imposta di soggiorno' : 'Limite massimo di notti imponibili raggiunto');
            $base['segmenti'] = [$base];

            return $base;
        }
        $segmenti = array_values($segmenti);
        $segmenti[count($segmenti) - 1]['notti_oltre_max'] = $base['notti_oltre_max'];
        $base['segmenti'] = $segmenti;
        $base['notti_tassate'] = array_sum(array_column($segmenti, 'notti_tassate'));
        $base['subtotale'] = array_sum(array_column($segmenti, 'subtotale'));
        $base['esente'] = $base['notti_tassate'] === 0;
        $base['notti_esenti'] = $base['notti_imponibili'] - $base['notti_tassate'];
        $base['esenzione_parziale'] = $base['notti_tassate'] > 0 && $base['notti_esenti'] > 0;
        $tariffe = array_values(array_unique(array_column(array_filter($segmenti, fn ($segmento) => $segmento['notti_tassate'] > 0), 'aliquota')));
        $base['aliquota'] = $base['esente'] ? 0.0 : (count($tariffe) === 1 ? $tariffe[0] : null);
        $base['codice'] = count($segmenti) === 1 ? $segmenti[0]['codice'] : 0;
        $base['motivo'] = count($segmenti) === 1 ? $segmenti[0]['motivo'] : implode('; ', array_unique(array_map(fn ($segmento) => $segmento['motivo'] ?? 'Ordinario', $segmenti)));

        return $base;
    }

    private function calcolaPersona(array $persona, ?Carbon $arrivo, ?Carbon $partenza, ?int $giorniMax, float $aliquota, ?TassaDiSoggiorno $config, Collection $esenzioni): array
    {
        $nottiTot = $this->diffNotti($arrivo, $partenza);
        $nottiNelPeriodo = $this->diffNottiNelPeriodo($arrivo, $partenza, $config);
        $esenzione = $this->resolveEsenzione($persona['exent'] ?? null, $esenzioni);
        $isEsente = $esenzione !== null;

        // Esenzione automatica per età
        $eta = $persona['eta'];
        if (! $isEsente && $eta !== null) {
            if ($config && $config->max_age_children && $eta <= (int) $config->max_age_children) {
                $isEsente = true;
                $esenzione = $esenzione ?? $this->resolveAutomaticEsenzione(
                    $esenzioni,
                    ['400', 'ETA_BIMBI'],
                    ['minori', 'bambini', 'minore'],
                    'ETA_BIMBI',
                    'Esente per età bambini'
                );
            }
            if ($config && $config->min_age_adult && $eta < (int) $config->min_age_adult) {
                $isEsente = true;
                $esenzione = $esenzione ?? $this->resolveAutomaticEsenzione(
                    $esenzioni,
                    ['ETA_MIN'],
                    ['eta minima', 'età minima'],
                    'ETA_MIN',
                    'Esente per età minima'
                );
            }
        }

        $nottiImponibili = $nottiNelPeriodo;
        if ($giorniMax !== null) {
            $nottiImponibili = min($nottiNelPeriodo, $giorniMax);
        }

        $nottiOltre = $giorniMax !== null
            ? max(0, $nottiNelPeriodo - $giorniMax)
            : 0;

        $nottiTassate = $isEsente ? 0 : $nottiImponibili;
        $aliquotaApplicata = $isEsente ? 0.0 : $aliquota;

        $motivo = $esenzione?->descrizione;
        if (! $isEsente && $nottiTot > 0 && $nottiNelPeriodo === 0) {
            $motivo = 'Fuori periodo di applicazione';
        } elseif (! $isEsente && $giorniMax !== null && $giorniMax === 0 && $nottiNelPeriodo > 0) {
            $motivo = 'Giorni massimo imponibili impostati a 0';
        } elseif (! $isEsente && $giorniMax !== null && $nottiNelPeriodo > 0 && $nottiImponibili === 0 && $nottiOltre > 0) {
            $motivo = 'Oltre giorni massimo';
        } elseif (! $isEsente && $nottiTot === 0) {
            $motivo = 'Soggiorno senza pernottamenti imponibili';
        }

        return [
            'nome' => $persona['nome'] ?: 'Ospite',
            'persona_id' => $persona['persona_id'] ?? null,
            'persona_tipo' => $persona['persona_tipo'] ?? null,
            'eta' => $eta,
            'esente' => $isEsente,
            'motivo' => $motivo,
            'codice' => $esenzione?->codice ?? 0,
            'notti_periodo' => $nottiNelPeriodo,
            'notti_totali' => $nottiTot,
            'notti_imponibili' => $nottiImponibili,
            'notti_tassate' => $nottiTassate,
            'notti_esenti' => $nottiImponibili - $nottiTassate,
            'esenzione_parziale' => false,
            'notti_oltre_max' => $nottiOltre,
            'aliquota' => $aliquotaApplicata,
            'subtotale' => $nottiTassate * $aliquotaApplicata,
        ];
    }

    private function resolveEsenzione(?string $value, Collection $esenzioni): ?TassaEsenzione
    {
        if (! $value || strtoupper(trim($value)) === 'NO') {
            return null;
        }

        $clean = trim($value);
        if ($clean === '777') {
            return null;
        }

        return $esenzioni->first(function ($row) use ($clean) {
            return strcasecmp($row->codice, '777') !== 0
                && (strcasecmp($row->codice, $clean) === 0 || strcasecmp($row->descrizione, $clean) === 0);
        });
    }

    private function isDataNelPeriodo(Carbon $giorno, Carbon $inizio, Carbon $fine): bool
    {
        $giornoKey = (int) $giorno->format('md');
        $inizioKey = (int) $inizio->format('md');
        $fineKey = (int) $fine->format('md');

        if ($inizioKey <= $fineKey) {
            return $giornoKey >= $inizioKey && $giornoKey <= $fineKey;
        }

        return $giornoKey >= $inizioKey || $giornoKey <= $fineKey;
    }

    private function etaFromString(?string $value): ?int
    {
        return EtaOperativa::etaOperativa($value);
    }

    private function makeVirtualEsenzione(string $codice, string $descrizione): TassaEsenzione
    {
        $fake = new TassaEsenzione;
        $fake->codice = $codice;
        $fake->descrizione = $descrizione;
        $fake->attivo = true;
        $fake->richiede_nota = false;
        $fake->ordine = 9999;

        return $fake;
    }

    private function resolveAutomaticEsenzione(Collection $esenzioni, array $codes, array $descriptionFragments, string $fallbackCode, string $fallbackDescription): TassaEsenzione
    {
        foreach ($codes as $code) {
            $match = $esenzioni->first(function ($row) use ($code) {
                return strcasecmp((string) $row->codice, $code) === 0;
            });

            if ($match) {
                return $match;
            }
        }

        foreach ($descriptionFragments as $fragment) {
            $match = $esenzioni->first(function ($row) use ($fragment) {
                return str_contains(mb_strtolower((string) $row->descrizione), mb_strtolower($fragment));
            });

            if ($match) {
                return $match;
            }
        }

        return $this->makeVirtualEsenzione($fallbackCode, $fallbackDescription);
    }
}
