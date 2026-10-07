<?php

namespace App\Services;

use App\Models\IstatExport;
use App\Models\Schedina;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class IstatPayloadStore
{
    public function store(int $strutturaId, ?int $userId, Carbon $dal, Carbon $al, string $filename, string $xml, $schedine): IstatExport
    {
        (new IstatXmlValidator)->validate($xml);
        if ($strutturaId <= 0 || $schedine->contains(fn ($s) => (int) $s->struttura_id !== $strutturaId)) {
            throw ValidationException::withMessages(['istat_export' => 'Dati non appartenenti alla struttura selezionata.']);
        }
        $createdPath = null;
        try {
            return DB::transaction(function () use ($strutturaId, $userId, $dal, $al, $filename, $xml, $schedine, &$createdPath) {
                \App\Models\Struttura::whereKey($strutturaId)->lockForUpdate()->firstOrFail();
                $prior = IstatExport::where('struttura_id', $strutturaId)->where('sha256', hash('sha256', $xml))
                    ->whereNull('minimized_at')->where('expires_at', '>', now())->first();
                if ($prior) {
                    $this->read($prior);

                    return $prior;
                }
                $path = 'istat/struttura_'.$strutturaId.'/'.now()->format('Ymd_His').'_'.Str::uuid().'_'.$filename;
                $createdPath = $path;
                if (! Storage::disk('local')->put($path, Crypt::encryptString($xml))) {
                    throw ValidationException::withMessages(['istat_export' => 'Impossibile conservare la copia XML protetta.']);
                }
                $export = IstatExport::create([
                    'struttura_id' => $strutturaId, 'user_id' => $userId, 'dal' => $dal, 'al' => $al,
                    'filename' => $filename, 'path' => $path, 'sha256' => hash('sha256', $xml), 'encrypted_file' => true,
                    'snapshot' => (new IstatSnapshot)->make($schedine),
                    'expires_at' => now()->addDays((int) config('istat.payload_days', 30)),
                    'schedine_count' => $schedine->count(), 'movimenti_count' => $dal->diffInDays($al) + 1,
                    'schedina_ids' => $schedine->pluck('id')->values()->all(),
                ]);
                Schedina::withoutGlobalScope('struttura')->where('struttura_id', $strutturaId)->whereIn('id', $schedine->pluck('id')->all())->update([
                    'istat_exported_at' => now(), 'istat_export_count' => DB::raw('COALESCE(istat_export_count, 0) + 1'),
                    'last_istat_export_id' => $export->id,
                ]);

                return $export;
            });
        } catch (Throwable $e) {
            if ($createdPath !== null) {
                Storage::disk('local')->delete($createdPath);
            }
            throw $e;
        }
    }

    public function read(IstatExport $export): string
    {
        $prefix = 'istat/struttura_'.(int) $export->struttura_id.'/';
        if (! str_starts_with((string) $export->path, $prefix) || str_contains($export->path, '..')
            || $export->minimized_at || ! Storage::disk('local')->exists($export->path)) {
            throw ValidationException::withMessages(['istat_export' => 'Copia XML non disponibile. Consultare lo storico sul portale Ross1000.']);
        }
        try {
            $stored = Storage::disk('local')->get($export->path);
            $xml = $export->encrypted_file ? Crypt::decryptString($stored) : $stored;
            if ($export->sha256 && ! hash_equals($export->sha256, hash('sha256', $xml))) {
                throw new \RuntimeException;
            }
            (new IstatXmlValidator)->validate($xml);

            return $xml;
        } catch (\Throwable) {
            throw ValidationException::withMessages(['istat_export' => 'Copia XML non verificabile. Nessuna trasmissione consentita.']);
        }
    }
}
