<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // acción registrada
            $table->enum('accion', [
                'estado_cambiado',
                'asignado',
                'desasignado',
                'cancelado',
            ]);

            // valores de referencia
            $table->string('de', 100)->nullable();  // p.ej. estado anterior o repartidor anterior
            $table->string('a', 100)->nullable();   // p.ej. estado nuevo o repartidor nuevo

            // motivo opcional (para cancelación)
            $table->string('motivo')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_logs');
    }
};
