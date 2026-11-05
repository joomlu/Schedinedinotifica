<?php

namespace App\Services;

use App\Models\{QuesturaExport, QuesturaReceipt, QuesturaTransmission, Struttura};
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;

class QuesturaRetentionService
{
    public function archiveReceipt(Struttura $structure, Carbon $date, string $bytes, ?int $transmissionId): QuesturaReceipt
    {
        abort_unless(strlen($bytes) <= 10 * 1024 * 1024 && str_starts_with($bytes, '%PDF-') && str_ends_with(rtrim($bytes), '%%EOF'), 409, 'Ricevuta PDF non valida.');
        abort_unless($date->isBefore(today()) && $date->gte(today()->subDays(30)), 409);
        if ($transmissionId !== null) {
            $tx = QuesturaTransmission::where('struttura_id', $structure->id)->findOrFail($transmissionId);
            abort_unless($tx->executed_at?->toDateString() === $date->toDateString(), 409);
        }
        $existing = QuesturaReceipt::where('struttura_id', $structure->id)->whereDate('remote_date', $date)->first();
        if ($existing) { $this->verifyReceipt($existing, $structure->id); return $existing; }
        $path = 'questura/struttura_'.$structure->id.'/ricevute/'.Str::uuid().'.pdf';
        try {
            abort_unless(Storage::disk('local')->put($path, $bytes), 500, 'Archiviazione ricevuta non completata.');
            abort_unless(Storage::disk('local')->exists($path) && hash_equals(hash('sha256', $bytes), hash('sha256', Storage::disk('local')->get($path))), 500, 'Integrità della scrittura ricevuta non verificata.');
            return DB::transaction(fn () => QuesturaReceipt::create([
                'struttura_id' => $structure->id, 'questura_transmission_id' => $transmissionId,
                'remote_date' => $date->toDateString(), 'filename' => 'ricevuta_questura_'.$date->format('Ymd').'.pdf',
                'path' => $path, 'mime' => 'application/pdf', 'sha256' => hash('sha256', $bytes), 'byte_size' => strlen($bytes),
                'acquired_at' => now(), 'retained_until' => now()->addYearsNoOverflow(5),
            ]));
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            $this->deleteTemporaryFile($path);
            $receipt = QuesturaReceipt::where('struttura_id', $structure->id)->whereDate('remote_date', $date)->firstOrFail();
            $this->verifyReceipt($receipt, $structure->id);
            return $receipt;
        } catch (\Throwable $error) {
            $this->deleteTemporaryFile($path);
            throw $error;
        }
    }

    public function finalizeDay(int $structureId, int $receiptId, int $actorId): void
    {
        $receipt = QuesturaReceipt::where('struttura_id', $structureId)->findOrFail($receiptId);
        $this->verifyReceipt($receipt, $structureId);
        QuesturaTransmission::where('struttura_id', $structureId)->where('mode', 'send')->where('status', 'sent')
            ->whereDate('executed_at', $receipt->remote_date)->whereNull('finalized_at')->orderBy('id')
            ->chunkById(50, function ($rows) use ($structureId, $receiptId, $actorId) {
                foreach ($rows as $tx) {
                    if (($tx->payload['transport_mode'] ?? '') === 'live') {
                        $this->finalizeTransmission($structureId, $tx->id, $receiptId, $actorId);
                    }
                }
            });
    }

    public function finalizeTransmission(int $structureId, int $id, int $receiptId, int $actorId, bool $reconciled = false): void
    {
        DB::transaction(function () use ($structureId, $id, $receiptId, $actorId, $reconciled) {
            $receipt = QuesturaReceipt::where('struttura_id', $structureId)->lockForUpdate()->findOrFail($receiptId);
            $this->verifyReceipt($receipt, $structureId);
            $tx = QuesturaTransmission::where('struttura_id', $structureId)->lockForUpdate()->findOrFail($id);
            if ($tx->finalized_at) { abort_unless((int) $tx->questura_receipt_id === $receiptId, 409); return; }
            abort_unless($tx->mode === 'send' && $tx->executed_at?->toDateString() === $receipt->remote_date->toDateString(), 409);
            abort_unless(($tx->payload['transport_mode'] ?? '') === 'live', 409, 'Una prova interna non è una comunicazione.');
            abort_unless($tx->status === 'sent' || ($reconciled && in_array($tx->status, ['uncertain', 'partial', 'in_progress'], true)), 409, 'Esito da riconciliare prima della finalizzazione.');
            $bytes = base64_decode($tx->payload['txt_base64'] ?? '', true);
            abort_unless(is_string($bytes) && $tx->sha256 && hash_equals($tx->sha256, hash('sha256', $bytes)), 409);
            $this->minimizeCopies($structureId, $tx->sha256, $receipt, $actorId, $reconciled, $tx->id, null);
        });
    }

    public function finalizeManual(int $structureId, int $id, int $receiptId, int $actorId, Carbon $communicationDate): void
    {
        DB::transaction(function () use ($structureId, $id, $receiptId, $actorId, $communicationDate) {
            $receipt = QuesturaReceipt::where('struttura_id', $structureId)->lockForUpdate()->findOrFail($receiptId);
            $this->verifyReceipt($receipt, $structureId);
            $export = QuesturaExport::where('struttura_id', $structureId)->lockForUpdate()->findOrFail($id);
            if ($export->finalized_at) { abort_unless((int) $export->questura_receipt_id === $receiptId, 409); return; }
            abort_unless($communicationDate->toDateString() === $receipt->remote_date->toDateString() && $export->created_at->lte($communicationDate->copy()->endOfDay()), 409);
            abort_unless($export->sha256, 409, 'Integrità del TXT da verificare prima della finalizzazione.');
            $this->minimizeCopies($structureId, $export->sha256, $receipt, $actorId, true, null, $communicationDate);
        });
    }

    private function minimizeCopies(int $structureId, string $hash, QuesturaReceipt $receipt, int $actorId, bool $reconciled, ?int $txId, ?Carbon $manualDate): void
    {
        $exports = QuesturaExport::where('struttura_id', $structureId)->where('sha256', $hash)->whereNull('finalized_at')->where('created_at', '<=', $receipt->acquired_at)->lockForUpdate()->get();
        $transmissions = QuesturaTransmission::where('struttura_id', $structureId)->where('sha256', $hash)->whereNull('finalized_at')
            ->where(function ($q) use ($txId) { $q->whereIn('mode', ['verify', 'test']); if ($txId) { $q->orWhere('id', $txId); } })
            ->where('created_at', '<=', $receipt->acquired_at)->lockForUpdate()->get();
        // Preflight dei percorsi e hash di tutti i file prima di cancellare il primo.
        foreach ($exports as $export) {
            $this->safePath($export->path, $structureId, false);
            if (Storage::disk('local')->exists($export->path)) {
                abort_unless(hash_equals($hash, hash('sha256', Storage::disk('local')->get($export->path))), 409, 'Integrità TXT non verificata.');
            }
        }
        foreach ($transmissions as $tx) {
            abort_unless(!$tx->receipt_path, 409, 'Ricevuta legacy da verificare separatamente.');
        }
        foreach ($exports as $export) { $this->deleteTemporaryFile($export->path); }
        $metadata = ['questura_receipt_id' => $receipt->id, 'finalized_at' => now(), 'payload_deleted_at' => now(),
            'schedina_ids' => null, 'component_ids' => null, 'dal' => null, 'al' => null, 'updated_at' => now()];
        if ($reconciled) { $metadata += ['reconciled_at' => now(), 'reconciled_by' => $actorId]; }
        // Scritture query builder riservate al servizio: il CRUD Eloquent resta immutabile.
        foreach ($exports as $export) {
            DB::table('questura_exports')->where('id', $export->id)->where('struttura_id', $structureId)->update($metadata + [
                'path' => null, 'filename' => 'questura_export_'.$export->id.'.txt', 'status' => 'finalized',
                'communication_date' => ($manualDate ?? $receipt->remote_date)->toDateString(),
            ]);
        }
        foreach ($transmissions as $tx) {
            DB::table('questura_transmissions')->where('id', $tx->id)->where('struttura_id', $structureId)->update($metadata + [
                'payload' => null, 'result' => null, 'response_code' => null, 'response_message' => null, 'response_detail' => null,
                'receipt_path' => null, 'receipt_filename' => null,
            ]);
            // Anche eventi legacy possono contenere risposte non minimizzate.
            DB::table('questura_transmission_events')->where('questura_transmission_id', $tx->id)->update(['result' => json_encode(['minimized' => true])]);
            DB::table('questura_transmission_events')->insert(['questura_transmission_id' => $tx->id, 'status' => 'finalized', 'result' => json_encode(['receipt_id' => $receipt->id, 'reconciled' => $reconciled]), 'created_at' => now()]);
        }
    }

    protected function deleteTemporaryFile(string $path): void
    {
        $disk = Storage::disk('local');
        if ($disk->exists($path) && (!$disk->delete($path) || $disk->exists($path))) {
            abort(503, 'Cancellazione Questura non completata; ripetere la finalizzazione dopo verifica storage.');
        }
    }

    public function verifyReceipt(QuesturaReceipt $receipt, int $structureId): void
    {
        abort_unless((int) $receipt->struttura_id === $structureId && !$receipt->purged_at && $receipt->acquired_at && $receipt->retained_until, 409);
        $this->safePath($receipt->path, $structureId, true);
        abort_unless(Storage::disk('local')->exists($receipt->path), 409, 'Ricevuta archiviata non disponibile.');
        $bytes = Storage::disk('local')->get($receipt->path);
        abort_unless($receipt->mime === 'application/pdf' && strlen($bytes) === (int) $receipt->byte_size && hash_equals($receipt->sha256, hash('sha256', $bytes))
            && str_starts_with($bytes, '%PDF-') && str_ends_with(rtrim($bytes), '%%EOF'), 409, 'Integrità ricevuta non verificata.');
    }

    private function safePath(?string $path, int $structureId, bool $receipt): void
    {
        $prefix = 'questura/struttura_'.$structureId.'/';
        abort_unless(is_string($path) && str_starts_with($path, $prefix) && !str_contains($path, '..') && !str_contains($path, '\\') && !str_contains($path, "\0")
            && ($receipt ? str_starts_with($path, $prefix.'ricevute/') && str_ends_with($path, '.pdf') : !str_starts_with($path, $prefix.'ricevute/') && str_ends_with($path, '.txt')), 409, 'Percorso Questura non valido.');
        $disk = Storage::disk('local');
        $parts = explode('/', $path); $relative = '';
        foreach ($parts as $part) { $relative .= ($relative === '' ? '' : '/').$part; abort_if(is_link($disk->path($relative)), 409, 'Percorso Questura non valido.'); }
    }

    public function auditArchives(int $structureId): array
    {
        $rows = []; $disk = Storage::disk('local');
        foreach (QuesturaExport::where('struttura_id', $structureId)->whereNull('finalized_at')->cursor() as $export) {
            $state = 'percorso_non_valido'; $hash = null;
            try {
                $this->safePath($export->path, $structureId, false);
                if (!$disk->exists($export->path)) { $state = 'file_assente'; }
                else {
                    $hash = hash('sha256', $disk->get($export->path));
                    $state = !$export->sha256 ? 'hash_legacy_assente' : (hash_equals($export->sha256, $hash) ? 'verificato' : 'hash_diverso');
                }
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException) { }
            $rows[] = ['tipo' => 'txt', 'id' => $export->id, 'stato' => $state, 'sha256_osservato' => $hash];
        }
        $receipts = QuesturaReceipt::where('struttura_id', $structureId)->get();
        foreach ($receipts as $receipt) {
            try { $this->verifyReceipt($receipt, $structureId); $state = 'verificato'; }
            catch (\Symfony\Component\HttpKernel\Exception\HttpException) { $state = 'ricevuta_non_verificata'; }
            $rows[] = ['tipo' => 'ricevuta', 'id' => $receipt->id, 'stato' => $state];
        }
        $known = $receipts->pluck('path')->all();
        foreach ($disk->allFiles('questura/struttura_'.$structureId.'/ricevute') as $path) {
            if (!in_array($path, $known, true)) {
                // Identificatore tecnico del percorso, senza nomi file potenzialmente personali.
                $rows[] = ['tipo' => 'orfano', 'id' => hash('sha256', $path), 'stato' => 'file_senza_record'];
            }
        }
        return $rows;
    }

    public function expiredReceipts(int $structureId, Carbon $at): \Illuminate\Support\Collection
    {
        // Solo selezione deterministica: nessuna cancellazione o schedulazione implicita.
        return DB::table('questura_receipts')->where('struttura_id', $structureId)->whereNull('purged_at')
            ->whereNotNull('acquired_at')->where('retained_until', '<=', $at)->orderBy('id')->get(['id', 'remote_date', 'acquired_at', 'retained_until']);
    }
}
