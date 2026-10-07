<?php

namespace App\Services;

use App\Models\IstatExport;
use App\Models\IstatTransmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class IstatRetention
{
    public function minimize(bool $apply = false): int
    {
        $count = 0;
        IstatExport::whereNotNull('snapshot')->where('encrypted_file', true)->where('expires_at', '<=', now())
            ->whereNull('minimized_at')->orderBy('id')->chunkById(100, function ($exports) use ($apply, &$count) {
                foreach ($exports as $export) {
                    DB::transaction(function () use ($export, $apply, &$count) {
                        // Stesso ordine di lock del writer: prima struttura, poi record.
                        \App\Models\Struttura::whereKey($export->struttura_id)->lockForUpdate()->firstOrFail();
                        $locked = IstatExport::whereKey($export->id)->lockForUpdate()->firstOrFail();
                        if ($locked->minimized_at || IstatTransmission::where('istat_export_id', $locked->id)
                            ->whereNull('reconciled_at')->whereIn('status', ['pending', 'uncertain', 'partial'])->exists()) {
                            return;
                        }
                        $prefix = 'istat/struttura_'.(int) $locked->struttura_id.'/';
                        if (! str_starts_with($locked->path, $prefix) || str_contains($locked->path, '..')) {
                            return;
                        }
                        if ($apply) {
                            if (Storage::disk('local')->exists($locked->path) && ! Storage::disk('local')->delete($locked->path)) {
                                return;
                            }
                            $locked->update(['minimized_at' => now()]);
                        }
                        $count++;
                    });
                }
            });

        return $count;
    }
}
