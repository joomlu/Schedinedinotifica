<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hotel_structures', function (Blueprint $table) {
            $table->id();
            
            // Relación con cliente (administrador de cadena - ej: Marriott, Hilton)
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('users')->onDelete('set null');
            
            // Información básica del hotel
            $table->string('name'); // Nombre del hotel (Hotel1, Hotel2, etc.)
            $table->text('description')->nullable(); // Descripción del hotel
            
            // Fechas administrativas
            $table->date('fecha_registro')->nullable(); // Fecha de registro/alta
            $table->date('fecha_vencimiento')->nullable(); // Fecha de vencimiento del contrato/licencia
            
            // Estados de autorización a nivel de autoridad/administrador
            $table->boolean('online')->default(true); // Si el hotel está on-line o off-line
            $table->boolean('activo')->default(true); // Si el hotel está activo o desactivado
            
            // Credenciales de acceso para el hotel a su software schedina
            $table->string('username_hotel')->nullable(); // Usuario para acceso del hotel
            $table->string('password_hotel')->nullable(); // Password para acceso del hotel
            
            // URL/link directo al software schedina de este hotel
            $table->string('schedina_url')->nullable(); // Link directo para que el hotel acceda a su schedina
            
            // Información de contacto y ubicación
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('cp')->nullable(); // Código postal
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('web')->nullable();
            
            // Información fiscal
            $table->string('cf')->nullable(); // Codice Fiscale
            $table->string('piva')->nullable(); // Partita IVA
            
            // Información operativa
            $table->string('typology')->nullable(); // Tipología del hotel
            $table->string('clasification')->nullable(); // Clasificación (estrellas, etc.)
            $table->integer('roomdisp')->nullable(); // Habitaciones disponibles
            $table->integer('beddisp')->nullable(); // Camas disponibles
            
            // Logo del hotel
            $table->string('logo')->nullable();
            
            $table->timestamps();
            
            // Índices para búsquedas
            $table->index('cliente_id');
            $table->index('online');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_structures');
    }
};
