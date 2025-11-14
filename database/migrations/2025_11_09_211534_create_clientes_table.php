<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear tabla clientes
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            // Relación con users
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Datos personales
            $table->string('nombre', 100)->nullable();
            $table->string('apellido', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 120)->nullable();

            // Dirección base
            $table->string('direccion', 255)->nullable();
            $table->string('colonia', 100)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('codigo_postal', 10)->nullable();

            // Referencias o notas
            $table->text('referencias')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Revertir tabla clientes
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
