<?php

namespace App\Services;

use App\Models\IstatMovimentoGiornaliero;
use App\Models\Schedina;
use App\Models\Struttura;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class IstatTabellaAService
{
    public const TIPO_TURISMO = [
        'Culturale' => 'Culturale', 'Balneare' => 'Balneare', 'Congressuale/Affari' => 'Congressuale/Affari',
        'Fieristico' => 'Fieristico', 'Sportivo/Fitness' => 'Sportivo/Fitness', 'Scolastico' => 'Scolastico',
        'Religioso' => 'Religioso', 'Sociale' => 'Sociale', 'Parchi Tematici' => 'Parchi Tematici',
        'Termale/Trattamenti salute' => 'Termale/Trattamenti salute', 'Enogastronomico' => 'Enogastronomico',
        'Cicloturismo' => 'Cicloturismo', 'Escursionistico/Naturalistico' => 'Escursionistico/Naturalistico',
        'Altro motivo' => 'Altro motivo', 'Non specificato' => 'Non specificato',
    ];
    public const MEZZO_TRASPORTO = [
        'Auto' => 'Auto', 'Aereo' => 'Aereo', 'Aereo+Pullman' => 'Aereo+Pullman',
        'Aereo+Navetta/Taxi/Auto' => 'Aereo+Navetta/Taxi/Auto', 'Aereo+Treno' => 'Aereo+Treno',
        'Treno' => 'Treno', 'Pullman' => 'Pullman', 'Caravan/Autocaravan' => 'Caravan/Autocaravan',
        'Barca/Nave/Traghetto' => 'Barca/Nave/Traghetto', 'Moto' => 'Moto', 'Bicicletta' => 'Bicicletta',
        'A piedi' => 'A piedi', 'Altro mezzo' => 'Altro mezzo', 'Non Specificato' => 'Non Specificato',
    ];
    public const CANALE_PRENOTAZIONE = [
        'Diretta tradizionale' => 'Diretta tradizionale', 'Diretta web' => 'Diretta web',
        'Indiretta tradizionale' => 'Indiretta tradizionale', 'Indiretta web' => 'Indiretta web',
        'Altro canale' => 'Altro canale', 'Non specificato' => 'Non specificato',
    ];
    public const TITOLO_STUDIO = [
        'Licenza elementare' => 'Licenza elementare', 'Diploma' => 'Diploma', 'Laurea' => 'Laurea',
        'Altro titolo' => 'Altro titolo', 'Non specificato' => 'Non specificato',
    ];

    public function schedinePerPeriodo(int $strutturaId, Carbon $dal, Carbon $al): Collection
    {
        return Schedina::query()->withoutGlobalScope('struttura')
            ->with(['componenti' => fn ($q) => $q->withoutGlobalScope('struttura')])
            ->where('struttura_id', $strutturaId)->where('is_arrive', false)
            ->where(fn ($q) => $q->where('circuito', 'schedina')->orWhereNull('circuito'))
            ->where(fn ($q) => $q->whereNull('istat_non_turista')->orWhere('istat_non_turista', false))
            // Le date mancanti non devono sparire silenziosamente dalla validazione.
            ->where(fn ($q) => $q->whereDate('arrive', '<=', $al->toDateString())->orWhereNull('arrive'))
            ->where(fn ($q) => $q->whereDate('departure', '>=', $dal->toDateString())->orWhereNull('departure'))
            ->orderBy('arrive')->orderBy('id')->get();
    }

    public function dailyRows(Struttura $struttura, Carbon $dal, Carbon $al): Collection
    {
        return $this->rows($struttura, $dal, $al, $this->schedinePerPeriodo($struttura->id, $dal, $al));
    }

    private function rows(Struttura $struttura, Carbon $dal, Carbon $al, Collection $schedine): Collection
    {
        $overrides = IstatMovimentoGiornaliero::query()->withoutGlobalScope('struttura')
            ->where('struttura_id', $struttura->id)->whereBetween('giorno', [$dal->toDateString(), $al->toDateString()])
            ->get()->keyBy(fn ($r) => $r->giorno->toDateString());
        $rows = collect();
        $codes = new IstatCodifiche();
        foreach (CarbonPeriod::create($dal->copy()->startOfDay(), $al->copy()->startOfDay()) as $date) {
            $active = $schedine->filter(fn ($s) => $this->date($s->arrive)?->lte($date) && $this->date($s->departure)?->gt($date));
            $arrivi = $schedine->filter(fn ($s) => $this->sameDate($s->arrive, $date));
            $partenze = $schedine->filter(fn ($s) => $this->sameDate($s->departure, $date));
            $guests = $active->flatMap(fn ($s) => $this->guests($s));
            $italiani = $guests->filter(fn ($g) => $codes->country($g['statoresidenza']) === '100000100')->count();
            $open = $this->isOpenForDay($struttura, $date);
            $rows->push([
                'giorno' => $date->toDateString(), 'aperta' => $open,
                'movimento_zero' => $open && $active->isEmpty() && $arrivi->isEmpty() && $partenze->isEmpty(),
                'camere_disponibili' => $open ? (int) $struttura->camere_disponibili : 0,
                'letti_disponibili' => $open ? (int) $struttura->letti_disponibili : 0,
                'camere_occupate' => (int) $active->merge($arrivi->filter(fn ($s) => $this->sameDate($s->departure, $date)))->unique('id')->sum(fn ($s) => (int) $s->room),
                'arrivi' => $arrivi->sum(fn ($s) => 1 + $s->componenti->count()),
                'partenze' => $partenze->sum(fn ($s) => 1 + $s->componenti->count()),
                'presenti' => $guests->count(), 'presenti_italiani' => $italiani,
                'presenti_stranieri' => $guests->count() - $italiani,
                'provenienze_nazioni' => $guests->filter(fn ($g) => $codes->country($g['statoresidenza']) !== '100000100')
                    ->groupBy(fn ($g) => $codes->country($g['statoresidenza']) ?? 'NON_RISOLTA')->map(fn ($g, $k) => ($codes->table('stati')[$k]['Descrizione'] ?? 'Provenienza non risolta').' '.$g->count())->implode(' · ') ?: '—',
                'provenienze_regioni' => $guests->filter(fn ($g) => $codes->country($g['statoresidenza']) === '100000100')->groupBy(fn ($g) => ctype_digit((string) $g['regione_residenza']) ? (\App\Models\GeoRegione::find((int) $g['regione_residenza'])?->nome ?? 'Non indicata') : ($g['regione_residenza'] ?: 'Non indicata'))->map(fn ($g, $k) => $k.' '.$g->count())->implode(' · ') ?: '—',
                'schedine_ids' => $active->pluck('id')->merge($arrivi->pluck('id'))->merge($partenze->pluck('id'))->unique()->values()->all(),
                'manuale' => $overrides->has($date->toDateString()), 'note' => $overrides->get($date->toDateString())?->note,
            ]);
        }
        return $rows;
    }

    public function analysePeriodo(Struttura $struttura, Carbon $dal, Carbon $al): array
    {
        $errors = [];
        if ($al->lt($dal)) {
            throw ValidationException::withMessages(['istat_export' => 'Periodo invertito: la data finale precede quella iniziale.']);
        }
        if (blank($struttura->istat_codice_struttura) || !$this->validText((string) $struttura->istat_codice_struttura)) {
            $errors[] = 'Codice struttura Ross1000 mancante o contenente caratteri XML non ammessi.';
        }
        foreach (['camere_disponibili', 'letti_disponibili'] as $field) {
            if (!preg_match('/^(0|[1-9][0-9]*)$/D', (string) $struttura->{$field})) {
                $errors[] = 'Struttura: '.$field.' deve essere un intero non negativo.';
            }
        }
        if (($struttura->tipo_apertura ?? 'Annuale') !== 'Annuale' && (!$this->date($struttura->data_apertura) || !$this->date($struttura->data_chiusura))) {
            $errors[] = 'Struttura stagionale: date di apertura/chiusura mancanti o non valide.';
        }
        $schedine = $this->schedinePerPeriodo($struttura->id, $dal, $al);
        $codes = new IstatCodifiche();
        $identifiers = [];
        foreach ($schedine as $s) {
            $prefix = ($s->scheda ?: 'Schedina #'.$s->id).': ';
            $arrive = $this->date($s->arrive);
            $departure = $this->date($s->departure);
            if (!$arrive || !$departure || $departure->lt($arrive)) {
                $errors[] = $prefix.'periodo soggiorno mancante, invertito o non valido.';
            }
            if (!$this->positiveInteger($s->cant_people) || (int) $s->cant_people !== 1 + $s->componenti->count()) {
                $errors[] = $prefix.'cant_people='.var_export($s->cant_people, true).' non coincide con gli ospiti nominativi ('.(1 + $s->componenti->count()).').';
            }
            foreach (['room', 'beds'] as $field) {
                if (!$this->positiveInteger($s->{$field})) {
                    $errors[] = $prefix.$field.'='.var_export($s->{$field}, true).' deve essere un intero positivo.';
                }
            }
            $headType = $codes->tipo($s->relationship);
            if (($s->componenti->isNotEmpty() && !in_array($headType, ['17', '18'], true)) || ($s->componenti->isEmpty() && $headType !== '16')) {
                $errors[] = $prefix.'tipoalloggiato non coerente con la composizione del soggiorno.';
            }
            foreach ($this->guests($s) as $index => $guest) {
                $label = $prefix.($index ? 'componente #'.$guest['component_id'].' ' : 'ospite principale ');
                if (strlen($guest['idswh']) > 20) {
                    $errors[] = $label.'idswh supera 20 caratteri.';
                }
                if (isset($identifiers[$guest['idswh']])) {
                    $errors[] = $label.'idswh duplicato.';
                }
                $identifiers[$guest['idswh']] = true;
                if ($guest['struttura_id'] !== (int) $struttura->id) {
                    $errors[] = $label.'struttura non coerente.';
                }
                if ($index && $codes->tipo($guest['tipoalloggiato']) !== ($headType === '17' ? '19' : '20')) {
                    $errors[] = $label.'tipoalloggiato incompatibile con il capo.';
                }
                foreach (['cognome' => 50, 'nome' => 30, 'professione' => null] as $field => $limit) {
                    $value = (string) $guest[$field];
                    if (!$this->validText($value) || ($limit && mb_strlen($value) > $limit)) {
                        $errors[] = $label.$field.' contiene caratteri non validi o supera la lunghezza ammessa.';
                    }
                }
                if (!in_array($guest['sesso'], ['M', 'F'], true)) {
                    $errors[] = $label.'sesso='.var_export($guest['sesso'], true).' non valido.';
                }
                $birth = $this->date($guest['datanascita']);
                if (!$birth || ($arrive && $birth->gt($arrive))) {
                    $errors[] = $label.'datanascita mancante, non valida o successiva all’arrivo.';
                }
                foreach (['cittadinanza', 'statoresidenza', 'statonascita'] as $field) {
                    if ($field === 'statonascita' && blank($guest[$field])) {
                        continue;
                    }
                    if (!$codes->country($guest[$field], $field === 'statonascita')) {
                        $errors[] = $label.$field.'='.var_export($guest[$field], true).' non risolvibile nella tabella ufficiale Stati.';
                    }
                }
                foreach (['luogoresidenza' => ['statoresidenza', 'provincia_residenza', false], 'comunenascita' => ['statonascita', 'provincia_nascita', true]] as $field => [$state, $prov, $isBirth]) {
                    if ($codes->country($guest[$state]) === '100000100' && !$codes->comune($guest[$field], $guest[$prov], $isBirth)) {
                        $errors[] = $label.$field.'='.var_export($guest[$field], true).' non univoco o non coerente con la provincia ufficiale.';
                    } elseif (!$isBirth && $codes->country($guest[$state]) !== '100000100' && (!$this->validText((string) $guest[$field]) || mb_strlen((string) $guest[$field]) > 30)) {
                        $errors[] = $label.$field.' estero non valido (massimo 30 caratteri).';
                    }
                }
                foreach (['tipoturismo' => self::TIPO_TURISMO, 'mezzotrasporto' => self::MEZZO_TRASPORTO, 'canaleprenotazione' => self::CANALE_PRENOTAZIONE, 'titolostudio' => self::TITOLO_STUDIO] as $field => $options) {
                    if ($this->option($guest[$field], $options, in_array($field, ['canaleprenotazione', 'titolostudio'], true)) === null) {
                        $errors[] = $label.$field.'='.var_export($guest[$field], true).' non corrisponde a una descrizione ufficiale univoca.';
                    }
                }
            }
        }
        $errors = array_merge($errors, (new IstatStoricoValidator())->errors($struttura->id, $dal, $al, $schedine));
        $rows = $this->rows($struttura, $dal, $al, $schedine);
        foreach ($rows->groupBy(fn ($row) => substr($row['giorno'], 0, 7)) as $month => $days) {
            if ($days->sum('camere_occupate') > $days->sum('camere_disponibili') || $days->sum('presenti') > $days->sum('letti_disponibili')) {
                $errors[] = $month.': occupazione superiore alla disponibilità complessiva; verificare ricettività e registrazioni.';
            }
        }
        foreach ($rows as $row) {
            if ($row['manuale']) {
                $errors[] = $row['giorno'].': esiste un override storico; riconciliare il dato prima dell’export, senza alterare lo storico automaticamente.';
            }
            if (!$row['aperta'] && ($row['presenti'] || $row['arrivi'] || $row['partenze'])) {
                $errors[] = $row['giorno'].': struttura chiusa ma sono presenti ospiti o movimenti.';
            }
        }
        return ['rows' => $rows, 'schedine' => $schedine, 'errors' => array_values(array_unique($errors)),
            'valida' => !$errors, 'totale_schedine' => $schedine->count(), 'totale_arrivi' => (int) $rows->sum('arrivi'),
            'totale_presenze' => (int) $rows->sum('presenti'), 'totale_partenze' => (int) $rows->sum('partenze')];
    }

    public function previewRecords(string $xml): array
    {
        $validator = new IstatXmlValidator();
        $validator->validate($xml);
        $xpath = new \DOMXPath($validator->document($xml));
        $records = [];
        foreach ($xpath->query('/movimenti/movimento') as $day) {
            foreach (['arrivi/arrivo' => 'Arrivo', 'partenze/partenza' => 'Partenza'] as $path => $kind) {
                foreach ($xpath->query($path, $day) as $record) {
                    $values = ['giorno' => $xpath->evaluate('string(data)', $day), 'tipo' => $kind];
                    foreach (['idswh', 'idcapo', 'tipoalloggiato', 'nome', 'cognome', 'sesso', 'cittadinanza',
                        'statoresidenza', 'luogoresidenza', 'datanascita', 'statonascita', 'comunenascita',
                        'tipoturismo', 'mezzotrasporto', 'canaleprenotazione', 'titolostudio', 'professione', 'arrivo'] as $field) {
                        $values[$field] = $xpath->evaluate('string('.$field.')', $record);
                    }
                    $records[] = $values;
                }
            }
        }
        return $records;
    }

    public function buildXml(Struttura $struttura, Carbon $dal, Carbon $al, ?array $analysis = null): string
    {
        $analysis ??= $this->analysePeriodo($struttura, $dal, $al);
        if (!$analysis['valida']) {
            throw ValidationException::withMessages(['istat_export' => $analysis['errors']]);
        }
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $root = $xml->appendChild($xml->createElement('movimenti'));
        $this->element($xml, $root, 'codice', $struttura->istat_codice_struttura);
        $this->element($xml, $root, 'prodotto', 'Schedinedinotifica');
        $codes = new IstatCodifiche();
        foreach ($analysis['rows'] as $row) {
            $movement = $root->appendChild($xml->createElement('movimento'));
            $date = Carbon::parse($row['giorno']);
            $this->element($xml, $movement, 'data', $date->format('Ymd'));
            $structure = $movement->appendChild($xml->createElement('struttura'));
            foreach (['apertura' => $row['aperta'] ? 'SI' : 'NO', 'camereoccupate' => $row['camere_occupate'], 'cameredisponibili' => $row['camere_disponibili'], 'lettidisponibili' => $row['letti_disponibili']] as $field => $value) {
                $this->element($xml, $structure, $field, $value);
            }
            foreach (['arrivi' => 'arrive', 'partenze' => 'departure'] as $section => $column) {
                $records = $analysis['schedine']->filter(fn ($s) => $this->sameDate($s->{$column}, $date));
                if ($records->isEmpty()) {
                    continue;
                }
                $container = $movement->appendChild($xml->createElement($section));
                foreach ($records as $s) {
                    foreach ($this->guests($s) as $g) {
                        $node = $container->appendChild($xml->createElement($section === 'arrivi' ? 'arrivo' : 'partenza'));
                        $this->element($xml, $node, 'idswh', $g['idswh']);
                        $this->element($xml, $node, 'tipoalloggiato', $codes->tipo($g['tipoalloggiato']));
                        if ($section === 'partenze') {
                            $this->element($xml, $node, 'arrivo', $this->date($s->arrive)->format('Ymd'));
                            continue;
                        }
                        $values = [
                            'idcapo' => $g['idcapo'], 'cognome' => $g['cognome'], 'nome' => $g['nome'], 'sesso' => $g['sesso'],
                            'cittadinanza' => $codes->country($g['cittadinanza']), 'statoresidenza' => $codes->country($g['statoresidenza']),
                            'luogoresidenza' => $codes->country($g['statoresidenza']) === '100000100' ? $codes->comune($g['luogoresidenza'], $g['provincia_residenza']) : $g['luogoresidenza'],
                            'datanascita' => $this->date($g['datanascita'])->format('Ymd'), 'statonascita' => $codes->country($g['statonascita'], true),
                            'comunenascita' => $codes->country($g['statonascita']) === '100000100' ? $codes->comune($g['comunenascita'], $g['provincia_nascita'], true) : '',
                            'tipoturismo' => $this->option($g['tipoturismo'], self::TIPO_TURISMO),
                            'mezzotrasporto' => $this->option($g['mezzotrasporto'], self::MEZZO_TRASPORTO),
                            'canaleprenotazione' => $this->option($g['canaleprenotazione'], self::CANALE_PRENOTAZIONE, true),
                            'titolostudio' => $this->option($g['titolostudio'], self::TITOLO_STUDIO, true), 'professione' => $g['professione'],
                        ];
                        foreach ($values as $field => $value) {
                            $this->element($xml, $node, $field, $value);
                        }
                    }
                }
            }
        }
        $result = $xml->saveXML();
        (new IstatXmlValidator())->validate($result);
        return $result;
    }

    private function guests(Schedina $s): Collection
    {
        $shared = ['tipoturismo' => $s->istat_tipo_turismo, 'mezzotrasporto' => $s->istat_mezzo_trasporto,
            'canaleprenotazione' => $s->istat_canale_prenotazione, 'titolostudio' => $s->istat_titolo_studio, 'professione' => (string) $s->istat_professione];
        $head = ['idswh' => 'S'.$s->id, 'idcapo' => '', 'component_id' => null, 'struttura_id' => (int) $s->struttura_id,
            'tipoalloggiato' => $s->relationship, 'nome' => (string) $s->name, 'cognome' => (string) $s->surname,
            'sesso' => $this->sex($s->sex), 'cittadinanza' => $s->oa_city_nac, 'statoresidenza' => $s->or_country,
            'luogoresidenza' => (string) $s->or_city, 'provincia_residenza' => $s->or_prov, 'regione_residenza' => $s->or_region, 'datanascita' => $s->oa_date_nac,
            'statonascita' => $s->oa_country, 'comunenascita' => $s->oa_city, 'provincia_nascita' => $s->oa_prov];
        $guests = collect([$head + $shared]);
        foreach ($s->componenti as $c) {
            $guests->push(['idswh' => 'C'.$c->id, 'idcapo' => 'S'.$s->id, 'component_id' => $c->id, 'struttura_id' => (int) $c->struttura_id,
                'tipoalloggiato' => $c->relationship, 'nome' => (string) $c->name, 'cognome' => (string) $c->surname,
                'sesso' => $this->sex($c->sex), 'cittadinanza' => $c->city_nac, 'statoresidenza' => $c->country,
                'luogoresidenza' => (string) $c->city, 'provincia_residenza' => $c->province, 'regione_residenza' => $c->regione, 'datanascita' => $c->date_nac,
                'statonascita' => $c->country_nac, 'comunenascita' => $c->comune_nac, 'provincia_nascita' => $c->province_nac] + array_replace($shared, ['titolostudio' => '', 'professione' => '']));
        }
        return $guests;
    }

    private function option(mixed $value, array $options, bool $optional = false): ?string
    {
        $value = trim((string) $value);
        if ($optional && $value === '') {
            return '';
        }
        $aliases = match ($options) {
            self::TIPO_TURISMO => ['OTHER' => 'Altro motivo'],
            self::MEZZO_TRASPORTO => ['AUTO' => 'Auto', 'TRENO' => 'Treno', 'AEREO' => 'Aereo', 'BUS' => 'Pullman', 'NAVE' => 'Barca/Nave/Traghetto', 'MOTO' => 'Moto', 'BICI' => 'Bicicletta', 'PIEDI' => 'A piedi', 'OTHER' => 'Altro mezzo'],
            self::CANALE_PRENOTAZIONE => ['OTA' => 'Indiretta web', 'OTHER' => 'Altro canale'],
            self::TITOLO_STUDIO => ['PRIMARY' => 'Licenza elementare', 'DIPLOMA' => 'Diploma', 'LAUREA' => 'Laurea'],
        };
        $value = $aliases[$value] ?? $value;
        foreach ($options as $label) {
            if (IstatCodifiche::normalize($value) === IstatCodifiche::normalize($label)) {
                return $label;
            }
        }
        return null;
    }

    private function date(mixed $value): ?Carbon
    {
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) {
            return null;
        }
        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
            return $date->format('Y-m-d') === $value ? $date : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function sameDate(mixed $value, Carbon $date): bool
    {
        return $this->date($value)?->isSameDay($date) ?? false;
    }

    private function isOpenForDay(Struttura $s, Carbon $date): bool
    {
        if (($s->tipo_apertura ?? 'Annuale') === 'Annuale') {
            return true;
        }
        $from = $this->date($s->data_apertura);
        $to = $this->date($s->data_chiusura);
        if (!$from || !$to) {
            return false;
        }
        return $to->gte($from) && $date->betweenIncluded($from, $to);
    }

    private function positiveInteger(mixed $value): bool
    {
        return preg_match('/^[1-9][0-9]*$/D', (string) $value) === 1;
    }

    private function validText(string $value): bool
    {
        return mb_check_encoding($value, 'UTF-8') && !preg_match('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', $value);
    }

    private function sex(mixed $value): string
    {
        return match (strtoupper(trim((string) $value))) { 'M', '1' => 'M', 'F', '2' => 'F', default => '' };
    }

    private function element(\DOMDocument $doc, \DOMNode $parent, string $name, mixed $value): void
    {
        $node = $parent->appendChild($doc->createElement($name));
        $node->appendChild($doc->createTextNode((string) $value));
    }

    public function buildSoapEnvelope(Struttura $struttura, string $xml, string $mode): string
    {
        if ($mode !== 'send') {
            throw ValidationException::withMessages(['istat_ws' => 'Il WSDL regionale non prevede una verifica remota senza trasmissione.']);
        }
        $validator = new IstatXmlValidator();
        $validator->validate($xml);
        $payload = $validator->document($xml);
        $soap = new \DOMDocument('1.0', 'UTF-8');
        $envelope = $soap->appendChild($soap->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope'));
        $body = $envelope->appendChild($soap->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Body'));
        $operation = $body->appendChild($soap->createElementNS(IstatXmlValidator::WS_NAMESPACE, 'ws:inviaMovimentazione'));
        $movement = $operation->appendChild($soap->createElement('movimentazione'));
        foreach ($payload->documentElement->childNodes as $node) {
            $movement->appendChild($soap->importNode($node, true));
        }
        return $soap->saveXML();
    }

    public function filename(Carbon $dal, Carbon $al): string
    {
        return 'tabella_a_'.$dal->format('Ymd').'_'.$al->format('Ymd').'.xml';
    }
}
