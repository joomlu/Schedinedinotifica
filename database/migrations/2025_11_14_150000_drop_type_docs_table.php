<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::dropIfExists('type_docs');
	}

	public function down(): void
	{
		Schema::create('type_docs', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('code')->nullable();
			$table->timestamps();
		});
	}
};
