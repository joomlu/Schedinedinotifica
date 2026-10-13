<?php

namespace App\Services\TassaConfig;

use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/** Unico archivio dei profili letti anche dal servizio fiscale; revisioni append-only. */
class ProfiloTassaRepository
{
    public const ROOT = 'tassa-profili';

    public function scheda(string $comune, int $anno): string
    {
        return hash('sha256', mb_strtolower(trim($comune)).'|'.$anno);
    }

    public function leggi(string $id): array
    {
        return Storage::disk('local')->exists(self::ROOT.'/schede/'.$id.'.json')
            ? json_decode(Storage::disk('local')->get(self::ROOT.'/schede/'.$id.'.json'), true, 512, JSON_THROW_ON_ERROR)
            : ['documenti' => [], 'problemi' => ['Non è stata ancora verificata la documentazione ufficiale di questo anno.'], 'ultima_verifica' => null];
    }

    public function scrivi(string $id, array $scheda): void
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory(self::ROOT);
        $lock = fopen($disk->path(self::ROOT.'/archivio.lock'), 'c');
        try {
            if (! flock($lock, LOCK_EX)) {
                throw new \RuntimeException('Archivio occupato. Riprovare.');
            }
            $attuale = $this->leggi($id);
            foreach ($scheda['documenti'] as $hash => &$d) {
                $prima = $attuale['documenti'][$hash] ?? null;
                if ($prima) {
                    $verifiche = array_merge($prima['verifiche'] ?? [], $d['verifiche'] ?? []);
                    $d['verifiche'] = array_values(array_reduce($verifiche, function ($v, $r) {
                        $v[json_encode($r)] = $r;

                        return $v;
                    }, []));
                    if ($prima['ufficiale'] && ! $d['ufficiale']) {
                        $d['ufficiale'] = true;
                        $d['fonte'] = $prima['fonte'];
                    }
                    if (isset($prima['attestazione_superadmin']) && ! isset($d['attestazione_superadmin'])) {
                        $d['attestazione_superadmin'] = $prima['attestazione_superadmin'];
                    }
                    if (isset($prima['revisione']) && ! isset($d['revisione'])) {
                        $d['revisione'] = $prima['revisione'];
                    }
                }
            }
            unset($d);
            $scheda['documenti'] = array_replace($attuale['documenti'], $scheda['documenti']);
            $this->atomico(self::ROOT.'/schede/'.$id.'.json', array_replace($attuale, $scheda));
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function profiliBellaria(bool $completo = false): array
    {
        $tutti = [];
        foreach (config('tassa_profili') as $p) {
            $tutti[$p['regola_versione']] = $p;
        }
        $pendenti = collect(Storage::disk('local')->files(self::ROOT.'/versioni'))->map(fn ($file) => json_decode(Storage::disk('local')->get($file), true, 512, JSON_THROW_ON_ERROR))->all();
        $sostituiti = [];
        while ($pendenti) {
            $progresso = false;
            foreach ($pendenti as $i => $r) {
                if (isset($r['riusa_versione']) && ! isset($tutti[$r['riusa_versione']])) {
                    continue;
                }
                $p = isset($r['riusa_versione']) ? array_replace($tutti[$r['riusa_versione']], $r['profilo']) : $r['profilo'];
                $tutti[$p['regola_versione']] = $p;
                foreach ($r['sostituisce'] ?? [] as $v) {
                    $sostituiti[$v] = true;
                }
                unset($pendenti[$i]);
                $progresso = true;
            }
            if (! $progresso) {
                throw new \RuntimeException('Provenienza di un profilo non ricostruibile.');
            }
        }

        return array_values($completo ? $tutti : array_diff_key($tutti, $sostituiti));
    }

    public function gestito(string $versione): bool
    {
        return ! collect(config('tassa_profili'))->contains('regola_versione', $versione)
            && collect($this->profiliBellaria())->contains('regola_versione', $versione);
    }

    public function applica(array $profilo, array $provenienza): string
    {
        // Nessuna riscrittura retroattiva, nemmeno se non esistono ricevute consolidate.
        if ($profilo['valida_dal'] <= now()->toDateString()) {
            throw ValidationException::withMessages(['validita' => 'Il periodo è già iniziato: lo storico viene conservato. Una rettifica del calcolo richiede un intervento separato.']);
        }
        $sostituisce = [];
        foreach ($this->profiliBellaria() as $esistente) {
            if (collect(array_diff_key($profilo, ['regola_versione' => 1, 'regola_fonte' => 1]))->every(fn ($v, $k) => array_key_exists($k, $esistente) && $v === $esistente[$k])) {
                return $esistente['regola_versione'];
            }
            if ($profilo['valida_dal'] <= $esistente['valida_al'] && $profilo['valida_al'] >= $esistente['valida_dal']) {
                if (($provenienza['modo'] ?? '') === 'confermata dal Superadmin' && $esistente['valida_dal'] > now()->toDateString() && $esistente['valida_dal'] === $profilo['valida_dal'] && $esistente['valida_al'] === $profilo['valida_al']) {
                    $sostituisce[] = $esistente['regola_versione'];
                } else {
                    throw ValidationException::withMessages(['validita' => 'Esiste un profilo sovrapposto: una revisione futura con la stessa validità richiede conferma. Periodi già iniziati e transizioni differenti restano conservati e richiedono un intervento separato.']);
                }
            }
        }
        $disk = Storage::disk('local');
        $path = self::ROOT.'/versioni/'.hash('sha256', json_encode($profilo)).'.json';
        // Lock condiviso anche con le verifiche periodiche.
        $disk->makeDirectory(self::ROOT);
        $lock = fopen($disk->path(self::ROOT.'/archivio.lock'), 'c');
        try {
            if (! flock($lock, LOCK_EX)) {
                throw new \RuntimeException('Archivio occupato. Riprovare.');
            }
            foreach ($this->profiliBellaria() as $esistente) {
                if (collect(array_diff_key($profilo, ['regola_versione' => 1, 'regola_fonte' => 1]))->every(fn ($v, $k) => array_key_exists($k, $esistente) && $v === $esistente[$k])) {
                    return $esistente['regola_versione'];
                }
                if ($profilo['valida_dal'] <= $esistente['valida_al'] && $profilo['valida_al'] >= $esistente['valida_dal'] && ! in_array($esistente['regola_versione'], $sostituisce, true)) {
                    throw ValidationException::withMessages(['validita' => 'Il profilo è stato aggiornato nel frattempo. Ricaricare la scheda.']);
                }
            }
            $record = compact('profilo', 'provenienza', 'sostituisce');
            foreach ($this->profiliBellaria() as $precedente) {
                $campi = ['tariffe', 'giorni_massimo', 'max_age_children', 'min_age_adult'];
                if (collect($campi)->every(fn ($campo) => $precedente[$campo] === $profilo[$campo])) {
                    $record['riusa_versione'] = $precedente['regola_versione'];
                    $record['profilo'] = array_diff_key($profilo, array_flip($campi));
                    break;
                }
            }
            $this->atomico($path, $record);

            return $profilo['regola_versione'];
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function atomico(string $path, array $contenuto): void
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory(dirname($path));
        $temp = tempnam(dirname($disk->path($path)), '.tassa-');
        try {
            file_put_contents($temp, json_encode($contenuto, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), LOCK_EX);
            chmod($temp, 0600);
            if (! rename($temp, $disk->path($path))) {
                throw new \RuntimeException('Salvataggio della scheda non riuscito.');
            }
        } finally {
            if (is_file($temp)) {
                unlink($temp);
            }
        }
    }
}
