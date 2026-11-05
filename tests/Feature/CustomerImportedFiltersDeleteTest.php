<?php

namespace Tests\Feature;

use App\Models\CustomerImportBatch;
use App\Models\CustomerImportRow;
use App\Models\User;
use App\Services\CustomerImportService;
use App\Support\StrutturaCorrente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class CustomerImportedFiltersDeleteTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_imported_index_filters_pending_rows_by_structure_and_filter_fields(): void
    {
        DB::beginTransaction();

        try {
            $strutturaA = $this->structureFor(null);
            $strutturaB = $this->structureFor(null);

            $user = User::factory()->create([
                'name' => 'Operatore A',
                'email' => 'operatore-a@example.com',
                'password' => bcrypt('secret'),
                'username' => 'operatore_a',
                'ruolo' => 'struttura_user',
                'struttura_id' => $strutturaA->id,
                'attivo' => true,
            ]);

            StrutturaCorrente::setId($strutturaA->id);
            $this->actingAs($user);

            $batchA = CustomerImportBatch::create([
                'struttura_id' => $strutturaA->id,
                'user_id' => $user->id,
                'original_name' => 'batch-a.csv',
                'stored_path' => 'customer-imports/a.csv',
                'status' => 'draft',
            ]);
            $batchB = CustomerImportBatch::create([
                'struttura_id' => $strutturaB->id,
                'user_id' => $user->id,
                'original_name' => 'batch-b.csv',
                'stored_path' => 'customer-imports/b.csv',
                'status' => 'draft',
            ]);

            $matching = CustomerImportRow::create([
                'batch_id' => $batchA->id,
                'row_number' => 1,
                'status' => CustomerImportService::STATUS_VALID,
                'raw_payload' => ['nome' => 'Mario'],
                'normalized_payload' => [
                    'nome' => 'Mario',
                    'cognome' => 'Rossi',
                    'tipo_cliente' => 'Ospite',
                    'tipo_alloggiato' => 'CAPOGRUPPO',
                    'data_nascita' => '1985-03-15',
                    'email' => 'mario@example.com',
                    'numero_documento' => 'DOC-123',
                    'numero_cliente' => 'CLI-001',
                    'comune_residenza' => 'Rimini',
                    'indirizzo_residenza' => 'Via Roma 12',
                    'telefono' => '0541 123456',
                    'cellulare' => '3331234567',
                ],
            ]);

            $otherStructure = CustomerImportRow::create([
                'batch_id' => $batchB->id,
                'row_number' => 1,
                'status' => CustomerImportService::STATUS_VALID,
                'raw_payload' => ['nome' => 'Mario'],
                'normalized_payload' => [
                    'nome' => 'Mario',
                    'cognome' => 'Rossi',
                    'tipo_cliente' => 'Ospite',
                    'tipo_alloggiato' => 'CAPOGRUPPO',
                    'data_nascita' => '1985-03-15',
                ],
            ]);

            $promoted = CustomerImportRow::create([
                'batch_id' => $batchA->id,
                'row_number' => 2,
                'status' => CustomerImportService::STATUS_VALID,
                'raw_payload' => ['nome' => 'Maria'],
                'normalized_payload' => [
                    'nome' => 'Maria',
                    'cognome' => 'Verdi',
                    'tipo_cliente' => 'Ospite',
                    'tipo_alloggiato' => 'OSPITE SINGOLO',
                    'data_nascita' => '1990-12-01',
                ],
                'imported_customer_id' => 99,
            ]);

            $response = $this->get(route('customer.imported.index', [
                'q' => 'Mario',
                'tipo_cliente' => 'Ospite',
                'tipo_alloggiato' => 'CAPOGRUPPO',
                'data_nascita' => '1985-03-15',
                'batch_id' => $batchA->id,
            ]));

            $response->assertOk();
            $response->assertViewHas('rows', function ($rows) use ($matching, $otherStructure, $promoted) {
                return $rows->count() === 1
                    && $rows->first()->id === $matching->id
                    && $rows->first()->id !== $otherStructure->id
                    && $rows->first()->id !== $promoted->id;
            });
        } finally {
            DB::rollBack();
        }
    }

    public function test_delete_selected_rows_only_removes_pending_rows_in_current_structure(): void
    {
        DB::beginTransaction();

        try {
            $strutturaA = $this->structureFor(null);
            $strutturaB = $this->structureFor(null);
            $user = User::factory()->create([
                'name' => 'Operatore A',
                'email' => 'operatore-b@example.com',
                'password' => bcrypt('secret'),
                'username' => 'operatore_b',
                'ruolo' => 'struttura_user',
                'struttura_id' => $strutturaA->id,
                'attivo' => true,
            ]);

            StrutturaCorrente::setId($strutturaA->id);
            $this->actingAs($user);

            $batchA = CustomerImportBatch::create([
                'struttura_id' => $strutturaA->id,
                'user_id' => $user->id,
                'original_name' => 'batch-a.csv',
                'stored_path' => 'customer-imports/a.csv',
                'status' => 'draft',
            ]);
            $batchB = CustomerImportBatch::create([
                'struttura_id' => $strutturaB->id,
                'user_id' => $user->id,
                'original_name' => 'batch-b.csv',
                'stored_path' => 'customer-imports/b.csv',
                'status' => 'draft',
            ]);

            $rowPendingA = CustomerImportRow::create([
                'batch_id' => $batchA->id,
                'row_number' => 1,
                'status' => CustomerImportService::STATUS_NEEDS_REVIEW,
                'raw_payload' => ['nome' => 'Alpha'],
                'normalized_payload' => ['nome' => 'Alpha'],
            ]);
            $rowPendingB = CustomerImportRow::create([
                'batch_id' => $batchA->id,
                'row_number' => 2,
                'status' => CustomerImportService::STATUS_NEEDS_REVIEW,
                'raw_payload' => ['nome' => 'Beta'],
                'normalized_payload' => ['nome' => 'Beta'],
            ]);
            $otherStructurePending = CustomerImportRow::create([
                'batch_id' => $batchB->id,
                'row_number' => 1,
                'status' => CustomerImportService::STATUS_NEEDS_REVIEW,
                'raw_payload' => ['nome' => 'Gamma'],
                'normalized_payload' => ['nome' => 'Gamma'],
            ]);
            $promoted = CustomerImportRow::create([
                'batch_id' => $batchA->id,
                'row_number' => 3,
                'status' => CustomerImportService::STATUS_IMPORTED,
                'raw_payload' => ['nome' => 'Delta'],
                'normalized_payload' => ['nome' => 'Delta'],
                'imported_customer_id' => 999,
            ]);

            $response = $this->post(route('customer.imported.bulk_destroy'), [
                'ids' => [$rowPendingA->id, $rowPendingB->id, $otherStructurePending->id, $promoted->id],
                'mode' => 'selected',
            ]);

            $response->assertRedirect();
            $this->assertDatabaseMissing('customer_import_rows', ['id' => $rowPendingA->id]);
            $this->assertDatabaseMissing('customer_import_rows', ['id' => $rowPendingB->id]);
            $this->assertDatabaseHas('customer_import_rows', ['id' => $otherStructurePending->id]);
            $this->assertDatabaseHas('customer_import_rows', ['id' => $promoted->id]);
        } finally {
            DB::rollBack();
        }
    }
}
