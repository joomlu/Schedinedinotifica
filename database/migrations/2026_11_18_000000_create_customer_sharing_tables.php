<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('struttura', fn (Blueprint $table) => $table->unique(['id', 'proprietario_id'], 'struttura_owner_sharing_unique'));
        Schema::table('clienti', fn (Blueprint $table) => $table->unique(['id', 'struttura_id'], 'clienti_structure_sharing_unique'));
        Schema::create('customer_sharing_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietario_id')->constrained('proprietari')->restrictOnDelete();
            $table->string('nome', 120);
            $table->string('stato', 16)->default('preparazione');
            $table->unsignedBigInteger('version')->default(1);
            $table->timestamps();
            $table->unique(['id', 'proprietario_id'], 'sharing_group_owner_unique');
        });
        Schema::create('customer_sharing_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('customer_sharing_groups')->restrictOnDelete();
            $table->foreignId('struttura_id')->unique()->constrained('struttura')->restrictOnDelete();
            $table->unsignedBigInteger('proprietario_id');
            $table->foreign(['group_id', 'proprietario_id'], 'sharing_membership_group_owner_fk')->references(['id', 'proprietario_id'])->on('customer_sharing_groups')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['struttura_id', 'proprietario_id'], 'sharing_membership_structure_owner_fk')->references(['id', 'proprietario_id'])->on('struttura')->restrictOnDelete()->restrictOnUpdate();
            $table->timestamps();
        });
        Schema::create('customer_persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('customer_sharing_groups')->restrictOnDelete();
            $table->longText('data');
            $table->char('document_key', 64)->nullable()->index();
            $table->char('birth_key', 64)->nullable()->index();
            $table->char('name_key', 64)->nullable()->index();
            $table->unsignedBigInteger('merged_into_id')->nullable();
            $table->string('residence_status', 24)->default('archivio');
            $table->timestamp('residence_confirmed_at')->nullable();
            $table->unsignedBigInteger('version')->default(1);
            $table->timestamps();
        });
        Schema::create('customer_person_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('customer_persons')->restrictOnDelete();
            $table->unsignedBigInteger('customer_id')->unique();
            $table->foreign('customer_id')->references('id')->on('clienti')->restrictOnDelete();
            $table->foreignId('struttura_id')->constrained('struttura')->restrictOnDelete();
            $table->foreign(['customer_id', 'struttura_id'], 'sharing_customer_structure_fk')->references(['id', 'struttura_id'])->on('clienti')->restrictOnDelete()->restrictOnUpdate();
            $table->boolean('provenienza')->default(false);
            $table->timestamp('detached_at')->nullable();
            $table->timestamps();
            $table->index(['person_id', 'struttura_id']);
        });
        Schema::create('customer_sharing_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('customer_sharing_groups')->restrictOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('tipo', 40);
            $table->longText('payload');
            $table->timestamps();
        });
        Schema::create('customer_person_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('customer_persons')->restrictOnDelete();
            $table->foreignId('struttura_id')->constrained('struttura')->restrictOnDelete();
            $table->longText('data');
            $table->timestamps();
        });
        Schema::create('customer_sharing_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('customer_sharing_groups')->restrictOnDelete();
            $table->foreignId('struttura_id')->constrained('struttura')->restrictOnDelete();
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('clienti')->restrictOnDelete();
            $table->longText('data');
            $table->timestamps();
        });
        Schema::create('customer_duplicate_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('customer_sharing_groups')->restrictOnDelete();
            $table->foreignId('first_id')->constrained('customer_persons')->restrictOnDelete();
            $table->foreignId('second_id')->constrained('customer_persons')->restrictOnDelete();
            $table->unsignedBigInteger('first_version');
            $table->unsignedBigInteger('second_version');
            $table->timestamps();
            $table->unique(['group_id', 'first_id', 'second_id'], 'customer_review_pair_unique');
        });
    }

    public function down(): void
    {
        // Un rollback dopo l'uso richiede riconciliazione, non perdita silenziosa.
        if (\Illuminate\Support\Facades\DB::table('customer_sharing_groups')->exists()) {
            throw new RuntimeException('Condivisione clienti presente: usare il recupero verificato prima di rimuovere lo schema.');
        }
        foreach (['customer_duplicate_reviews', 'customer_sharing_packages', 'customer_person_versions', 'customer_sharing_operations', 'customer_person_links', 'customer_persons', 'customer_sharing_memberships', 'customer_sharing_groups'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('clienti', fn (Blueprint $table) => $table->dropUnique('clienti_structure_sharing_unique'));
        Schema::table('struttura', fn (Blueprint $table) => $table->dropUnique('struttura_owner_sharing_unique'));
    }
};
