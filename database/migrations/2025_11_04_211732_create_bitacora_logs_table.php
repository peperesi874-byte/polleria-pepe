<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bitacora_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('modulo', 50);           // usuarios, pedidos, productos, inventario...
            $table->string('accion', 50);           // crear, editar, eliminar, exportar, movimiento
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->json('meta')->nullable();       // detalles adicionales
            $table->string('ip', 64)->nullable();
            $table->text('ua')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('bitacora_logs');
    }
};
