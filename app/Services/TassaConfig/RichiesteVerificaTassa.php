<?php

namespace App\Services\TassaConfig;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Richieste private del Superadmin; il processo CLI usa i presidi di rete già esistenti. */
class RichiesteVerificaTassa
{
    public function __construct(private GestioneProfiliTassa $gestione) {}

    public function richiedi(string $comune, int $anno, int $autore, ?string $fonte = null, ?string $originale = null): void
    {
        if ($fonte) {
            $p = parse_url($fonte);
            if (($p['scheme'] ?? '') !== 'https' || isset($p['user']) || isset($p['pass']) || isset($p['fragment']) || (isset($p['port']) && $p['port'] !== 443)) {
                throw ValidationException::withMessages(['fonte' => 'Indicare una fonte HTTPS senza credenziali, frammenti o porte particolari.']);
            }
        }
        $d = Storage::disk('local');
        $dir = ProfiloTassaRepository::ROOT.'/richieste';
        $d->makeDirectory($dir);
        $id = Str::uuid()->toString();
        $payload = compact('comune', 'anno', 'autore', 'fonte', 'originale');
        $temp = tempnam($d->path($dir), '.richiesta-');
        try {
            file_put_contents($temp, json_encode($payload, JSON_THROW_ON_ERROR), LOCK_EX);
            chmod($temp, 0600);
            if (! rename($temp, $d->path($dir.'/'.$id.'.json'))) {
                throw new \RuntimeException('Richiesta non registrata.');
            }
        } finally {
            if (is_file($temp)) {
                unlink($temp);
            }
        }
    }

    public function inCoda(string $comune, int $anno): bool
    {
        foreach (Storage::disk('local')->files(ProfiloTassaRepository::ROOT.'/richieste') as $path) {
            if (! str_ends_with($path, '.json')) {
                continue;
            }
            $r = json_decode(Storage::disk('local')->get($path), true, 512, JSON_THROW_ON_ERROR);
            if ($r['comune'] === $comune && $r['anno'] === $anno) {
                return true;
            }
        }

        return false;
    }

    public function processa(): void
    {
        $disk = Storage::disk('local');
        foreach (array_slice($disk->files(ProfiloTassaRepository::ROOT.'/richieste'), 0, 10) as $path) {
            if (! str_ends_with($path, '.json')) {
                continue;
            }
            $r = json_decode($disk->get($path), true, 512, JSON_THROW_ON_ERROR);
            $autore = User::find($r['autore']);
            $problemi = [];
            try {
                if (! $autore || $autore->ruolo !== 'super_admin' || ! $autore->attivo) {
                    throw ValidationException::withMessages(['autorizzazione' => 'Richiesta annullata: l’autore non dispone più di un accesso Superadmin attivo.']);
                }
                if (! $r['fonte']) {
                    $this->gestione->verifica($r['comune'], $r['anno']);
                } else {
                    $hosts = config('tassa_fonti.'.$r['comune'].'.hosts', []);
                    $links = [];
                    if (! in_array(parse_url($r['fonte'], PHP_URL_HOST), $hosts, true)) {
                        $indice = config('tassa_fonti.'.$r['comune'].'.indice');
                        if (! $indice) {
                            throw ValidationException::withMessages(['fonte' => 'Fonte istituzionale di questo Comune ancora da verificare.']);
                        }
                        $pagina = $this->gestione->fonti->scarica($indice, $r['comune']);
                        $links = $this->gestione->fonti->collegamenti($pagina['bytes'], $indice);
                    }
                    $doc = $this->gestione->fonti->scarica($r['fonte'], $r['comune'], $links);
                    $record = $this->gestione->conserva($r['comune'], $r['anno'], $doc['bytes'], $doc['tipo'], $r['fonte'], true);
                    if ($r['originale'] && $r['originale'] !== $record['id']) {
                        $problemi[] = 'Il documento ufficiale è diverso dall’originale caricato: entrambe le copie sono conservate, quella caricata non viene certificata automaticamente.';
                    } else {
                        $this->gestione->provaApplicazione($r['comune'], $r['anno'], $record);
                    }
                }
            } catch (ValidationException $e) {
                $problemi = array_merge(...array_values($e->errors()));
            } catch (\Throwable $e) {
                $problemi = ['Verifica non conclusa per un errore operativo. Nessun nuovo profilo applicato; riprovare o chiedere una verifica del registro riservato.'];
                report($e);
            }
            if ($problemi) {
                $id = $this->gestione->archivio->scheda($r['comune'], $r['anno']);
                $s = $this->gestione->archivio->leggi($id);
                $s['ultima_verifica'] = now()->toIso8601String();
                $s['verifica'] = $problemi;
                $this->gestione->archivio->scrivi($id, $s);
            }
            // L’esito della richiesta viene conservato, non cancellato insieme all’originale.
            $disk->makeDirectory(ProfiloTassaRepository::ROOT.'/richieste-concluse');
            $disk->move($path, ProfiloTassaRepository::ROOT.'/richieste-concluse/'.basename($path));
        }
    }
}
