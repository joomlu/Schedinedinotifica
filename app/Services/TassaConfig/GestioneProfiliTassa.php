<?php

namespace App\Services\TassaConfig;

use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class GestioneProfiliTassa
{
    public function __construct(public ProfiloTassaRepository $archivio, public FonteUfficialeTassa $fonti, public AnalisiDocumentoTassa $analisi) {}

    public function conserva(string $comune, int $anno, string $bytes, string $tipo, ?string $url, bool $ufficiale = false, ?string $titolo = null): array
    {
        $id = $this->archivio->scheda($comune, $anno);
        $s = $this->archivio->leggi($id);
        $hash = hash('sha256', $bytes);
        $path = ProfiloTassaRepository::ROOT.'/documenti/'.$hash.'.'.($tipo === 'pdf' ? 'pdf' : 'txt');
        $disk = Storage::disk('local');
        if (! $disk->exists($path)) {
            $disk->put($path, $bytes);
        }
        $testo = $this->analisi->testo($bytes, $tipo);
        $estratto = $this->analisi->estrai($testo['testo'], $anno);
        // Riutilizzo solo con conferma esplicita del nuovo anno e di tutti i parametri;
        // non riempire una nuova delibera incompleta con importi precedenti.
        if ($comune === 'Bellaria-Igea Marina' && $ufficiale && $estratto['problemi']) {
            $precedente = collect($this->archivio->profiliBellaria())->first(fn ($p) => $p['valida_al'] === ($anno - 1).'-12-31');
            $pattern = '/(?:sono|si)\s+conferma(?:te|no)\s+per\s+l[’\']anno\s+'.$anno.'\s+le\s+tariffe,\s+il\s+periodo,\s+i\s+limiti\s+e\s+le\s+esenzioni\s+dell[’\']anno\s+'.($anno - 1).'/iu';
            if ($precedente && preg_match('/imposta\s+di\s+soggiorno/iu', $testo['testo']) && preg_match($pattern, $testo['testo'], $match) && ! preg_match('/salvo|eccetto|ad eccezione|modifica|nuov[ae] tarif/iu', $testo['testo'])) {
                $v = ['valida_dal' => $anno.'-01-01', 'valida_al' => $anno.'-12-31', 'inizio' => $anno.substr($precedente['inizio'], 4), 'fine' => $anno.substr($precedente['fine'], 4), 'giorni_massimo' => $precedente['giorni_massimo'], 'max_age_children' => $precedente['max_age_children'], 'min_age_adult' => $precedente['min_age_adult']];
                foreach ($precedente['tariffe'] as $c => $importo) {
                    $v['tariffa_'.$c] = $importo;
                }
                $refs = [];
                foreach ($v as $campo => $_) {
                    $refs[$campo] = ['riferimento' => 'Conferma annuale esplicita; provenienza '.$precedente['regola_versione'], 'passaggio' => $match[0]];
                }
                // L’efficacia deve essere espressa, distinta dalla sola data di approvazione.
                if (preg_match('/(?:efficace|in vigore)\s+dal\s+01\/01\/'.$anno.'/iu', $testo['testo'])) {
                    $estratto = ['valori' => $v, 'riferimenti' => $refs, 'problemi' => [], 'continuita_da' => $precedente['regola_versione']];
                }
            }
        }

        $record = ['titolo' => $titolo ?: ($url ? basename(parse_url($url, PHP_URL_PATH) ?: 'Documento ufficiale') : 'Testo fornito dal Superadmin'), 'id' => $hash, 'path' => $path, 'tipo' => $tipo, 'fonte' => $url, 'ufficiale' => $ufficiale, 'verificata_il' => now()->toIso8601String(), 'testo' => $testo['testo'], 'analisi' => $estratto, 'limite' => $testo['limite']];
        $s['comune'] = $comune;
        $s['anno'] = $anno;
        $precedente = $s['documenti'][$hash] ?? null;
        $record['verifiche'] = array_merge($precedente['verifiche'] ?? [], [['fonte' => $url, 'data' => $record['verificata_il'], 'ufficiale' => $ufficiale]]);
        if ($precedente && $precedente['ufficiale'] && ! $ufficiale) {
            $record['ufficiale'] = true;
            $record['fonte'] = $precedente['fonte'];
        }
        $s['documenti'][$hash] = $record;
        $s['ultima_verifica'] = now()->toIso8601String();
        $s['problemi'] = array_merge($estratto['problemi'], $testo['limite'] ? [$testo['limite']] : [], $ufficiale ? [] : ['Provenienza ufficiale non verificata: il documento caricato o il testo incollato non costituiscono da soli una fonte certificata.']);
        $this->archivio->scrivi($id, $s);

        return $record;
    }

    public function verifica(string $comune, int $anno): array
    {
        $id = $this->archivio->scheda($comune, $anno);
        $indice = config('tassa_fonti.'.$comune.'.indice');
        $problemi = [];
        $raccolti = [];
        $s = $this->archivio->leggi($id);
        $s['verifica_completa'] = false;
        $this->archivio->scrivi($id, $s);
        if (! $indice) {
            $problemi[] = 'Sito istituzionale di questo Comune non ancora verificato. Indicare una fonte e documentarne la provenienza; nessuna ricerca negativa viene considerata prova di assenza.';
        } else {
            try {
                foreach (config('tassa_fonti.'.$comune.'.indici', [$indice]) as $indiceCorrente) {
                    try {
                        $pagina = $this->fonti->scarica($indiceCorrente, $comune);
                        $raccolti[] = $this->conserva($comune, $anno, $pagina['bytes'], $pagina['tipo'], $indiceCorrente, true);
                        $links = $this->fonti->collegamenti($pagina['bytes'], $indiceCorrente);
                        if (count($links) > 12) {
                            $problemi[] = 'La pagina contiene più di dodici documenti pertinenti: controllo automatico limitato, occorre verificare i restanti collegamenti.';
                        }
                        foreach (array_slice($links, 0, 12) as $url) {
                            try {
                                $doc = $this->fonti->scarica($url, $comune, $links);
                                $r = $this->conserva($comune, $anno, $doc['bytes'], $doc['tipo'], $url, true);
                                $raccolti[] = $r;
                            } catch (ValidationException $e) {
                                $problemi = array_merge($problemi, array_merge(...array_values($e->errors())));
                            }
                        }
                    } catch (ValidationException $e) {
                        $problemi = array_merge($problemi, array_merge(...array_values($e->errors())));
                    }
                }
                $s = $this->archivio->leggi($id);
                $s['verifica_completa'] = ! $problemi && ! collect($raccolti)->contains(fn ($r) => $r['limite'] !== null);
                $this->archivio->scrivi($id, $s);
                // Valutare il gruppo solo dopo avere raccolto tutti gli allegati: mai applicare il primo risultato.
                if (! $problemi) {
                    $candidati = array_filter($raccolti, fn ($r) => ! $r['analisi']['problemi']);
                    $impronte = array_unique(array_map(fn ($r) => json_encode($r['analisi']['valori']), $candidati));
                    $illeggibili = array_filter($raccolti, fn ($r) => $r['limite'] !== null);
                    if (count($impronte) === 1 && ! $illeggibili) {
                        $candidato = reset($candidati);
                        $conflitti = false;
                        foreach ($raccolti as $r) {
                            foreach ($r['analisi']['valori'] as $campo => $v) {
                                if (isset($candidato['analisi']['valori'][$campo]) && $v !== $candidato['analisi']['valori'][$campo]) {
                                    $conflitti = true;
                                }
                            }
                        }
                        if ($conflitti) {
                            $problemi[] = 'I documenti ufficiali contengono parametri discordanti. Risolvere il riferimento applicabile prima della conferma.';
                        } else {
                            try {
                                $this->provaApplicazione($comune, $anno, $candidato);
                            } catch (ValidationException $e) {
                                $problemi = array_merge(...array_values($e->errors()));
                            }
                        }
                    } elseif (count($impronte) > 1) {
                        $problemi[] = 'Sono presenti più disposizioni complete ma discordanti: il Superadmin deve accertare quella applicabile.';
                    } elseif ($illeggibili) {
                        $problemi[] = 'Almeno un allegato non è leggibile: la verifica automatica non può escludere disposizioni diverse.';
                    }
                }
                $problemi[] = 'Controllate la pagina comunale e i documenti direttamente collegati. Non è attestata l’assenza di altre disposizioni o allegati nell’albo; se la continuità annuale non è esplicita occorre conferma documentata.';
            } catch (ValidationException $e) {
                $problemi = array_merge(...array_values($e->errors()));
            }
        }
        $s = $this->archivio->leggi($id);
        $s['comune'] = $comune;
        $s['anno'] = $anno;
        $s['ultima_verifica'] = now()->toIso8601String();
        $s['verifica'] = array_values(array_unique($problemi));
        $this->archivio->scrivi($id, $s);

        return $s;
    }

    public function provaApplicazione(string $comune, int $anno, array $documento): void
    {
        $a = $documento['analisi'];
        if (! $documento['ufficiale'] || isset($documento['attestazione_superadmin']) || $a['problemi']) {
            return;
        }
        $scheda = $this->archivio->leggi($this->archivio->scheda($comune, $anno));
        if (! ($scheda['verifica_completa'] ?? false)) {
            throw ValidationException::withMessages(['fonte' => 'La verifica delle fonti note non è conclusa o contiene allegati illeggibili. Risolvere i punti segnalati prima di applicare automaticamente.']);
        }
        foreach ($scheda['documenti'] as $altro) {
            if (! $altro['ufficiale']) {
                continue;
            }
            foreach ($altro['analisi']['valori'] as $campo => $valore) {
                if (isset($a['valori'][$campo]) && $valore !== $a['valori'][$campo]) {
                    throw ValidationException::withMessages(['documento' => 'Un altro documento ufficiale conservato contiene parametri discordanti. Verificare quale disposizione è applicabile.']);
                }
            }
        }
        $this->applica($comune, $anno, $documento, $a['valori'], 'automatica');
    }

    public function applica(string $comune, int $anno, array $documento, array $v, string $modo): void
    {
        $problemi = [];
        if ($comune !== 'Bellaria-Igea Marina') {
            $problemi[] = 'Il motore attuale dispone di un profilo certificato soltanto per gli alberghi di Bellaria-Igea Marina. Estendere il calcolo ad altri Comuni o categorie è fuori da questo intervento.';
        }
        if (! $documento['ufficiale']) {
            $problemi[] = 'Prima di applicare occorre verificare la provenienza presso il Comune: nessuna certificazione basata sul solo upload.';
        }
        if ((int) ($v['max_age_children'] ?? -1) !== 17 || (int) ($v['min_age_adult'] ?? -1) !== 18) {
            $problemi[] = 'Età/esenzioni differenti dal comportamento fiscale esistente: modifica del calcolo fuori ambito.';
        }
        foreach (['valida_dal', 'valida_al', 'inizio', 'fine', 'giorni_massimo', 'tariffa_1', 'tariffa_2', 'tariffa_3', 'tariffa_4', 'tariffa_5'] as $campo) {
            if (! isset($v[$campo])) {
                $problemi[] = 'Configurazione incompleta: completare i parametri e i relativi riferimenti ufficiali.';
                break;
            }
        }
        if (! $problemi && ($v['valida_dal'] > $v['inizio'] || $v['fine'] > $v['valida_al'] || $v['inizio'] > $v['fine'] || substr($v['valida_dal'], 0, 4) != $anno || substr($v['valida_al'], 0, 4) != $anno)) {
            $problemi[] = 'Periodo e validità devono appartenere all’anno selezionato e il periodo deve essere incluso nella validità.';
        }
        if ($modo === 'automatica' && (! preg_match('/(?:deliberazione|regolamento)[\s\S]{0,160}approvat[oa]|approvat[oa][\s\S]{0,160}(?:deliberazione|regolamento)/iu', $documento['testo']) || preg_match('/bozza|proposta|revocat[oa]|sospes[oa]|esempio|simulazione|non ufficiale/iu', $documento['testo']))) {
            $problemi[] = 'Il documento non identifica inequivocabilmente una disposizione definitiva applicabile: occorre revisione del Superadmin.';
        }
        if (isset($v['giorni_massimo']) && (int) $v['giorni_massimo'] !== 6) {
            $problemi[] = 'Limite notti diverso da 6: il raccordo StayTour esistente non è documentato per altri limiti. Non viene modificato il tracciato; applicazione fuori ambito.';
        }
        foreach (range(1, 5) as $c) {
            if (isset($v['tariffa_'.$c]) && (! is_numeric($v['tariffa_'.$c]) || ! is_finite((float) $v['tariffa_'.$c]) || (float) $v['tariffa_'.$c] < 0)) {
                $problemi[] = 'Tariffa alberghi '.$c.' stelle fuori dai limiti supportati.';
            }
        }
        if (preg_match('/(?:imposta|tariffa)[^\n.]{0,60}(?:percentual|per\s+camera|per\s+appartamento)/iu', $documento['testo'])) {
            $problemi[] = 'Base della tariffa differente dalla quota fissa per persona e notte: il calcolo attuale non supporta questa disposizione.';
        }
        foreach (range(1, 5) as $c) {
            preg_match_all('/(?:albergo|alberghi)\s+'.$c.'\s+stell[ae]\s+superior\s*[:–-]?\s*(?:€|euro)\s*(\d+(?:[,.]\d{1,2})?)/iu', $documento['testo'], $superior);
            foreach ($superior[1] as $prezzo) {
                if (isset($v['tariffa_'.$c]) && (float) str_replace(',', '.', $prezzo) !== (float) $v['tariffa_'.$c]) {
                    $problemi[] = 'Tariffa distinta per '.$c.' stelle superior: questa differenziazione non è supportata dal profilo attuale. Non vengono modificate categorie o calcoli.';
                }
            }
        }
        if ($problemi) {
            throw ValidationException::withMessages(['configurazione' => $problemi]);
        }
        foreach (['giorni_massimo', 'max_age_children', 'min_age_adult'] as $campo) {
            $v[$campo] = (int) $v[$campo];
        }
        $tariffe = [];
        foreach (range(1, 5) as $c) {
            $tariffe[$c] = number_format((float) $v['tariffa_'.$c], 2, '.', '');
        }
        $profilo = array_intersect_key($v, array_flip(['valida_dal', 'valida_al', 'inizio', 'fine', 'giorni_massimo', 'max_age_children', 'min_age_adult']));
        $profilo['tariffe'] = $tariffe;
        $profilo['regola_versione'] = 'bellaria-alberghi-'.$anno.'-'.substr($documento['id'], 0, 12);
        $profilo['regola_fonte'] = $documento['fonte'] && ! isset($documento['attestazione_superadmin']) ? 'Fonte istituzionale: '.parse_url($documento['fonte'], PHP_URL_HOST) : 'Documento comunale ricevuto — provenienza e vigenza riscontrate dal Superadmin';
        $versione = $this->archivio->applica($profilo, ['documento' => $documento['id'], 'riferimenti' => $documento['analisi']['riferimenti'], 'modo' => $modo, 'verificata_il' => $documento['verificata_il'], 'revisione' => $documento['revisione'] ?? null, 'attestazione_superadmin' => $documento['attestazione_superadmin'] ?? null]);
        $id = $this->archivio->scheda($comune, $anno);
        $s = $this->archivio->leggi($id);
        $s['applicazione'] = ['versione' => $versione, 'modo' => $modo, 'data' => now()->toIso8601String()];
        $this->archivio->scrivi($id, $s);
    }
}
