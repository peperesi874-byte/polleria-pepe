<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();

            // Folio interno (opcional por ahora)
            $table->string('folio', 30)->nullable()->unique();

            // Relaciones principales
            //$table->foreignId('id_cliente')->nullable()->constrained('clientes')->nullOnDelete();
            $table->unsignedBigInteger('id_cliente')->nullable(); // por ahora sin relación
            $table->foreignId('id_direccion')->nullable()->constrained('direcciones')->nullOnDelete();
            $table->foreignId('asignado_a')->nullable()->constrained('users')->nullOnDelete(); // repartidor
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete(); // admin o vendedor

            // Datos generales del pedido
            $table->enum('tipo_entrega', ['mostrador', 'domicilio'])->default('mostrador');
            $table->decimal('total', 10, 2)->default(0);

            // Estado del pedido
            $table->enum('estado', [
                'pendiente',
                'preparando',
                'listo',
                'en_camino',
                'entregado',
                'cancelado'
            ])->default('pendiente');

            // Motivo de cancelación (si aplica)
            $table->string('motivo_cancelacion')->nullable();

            // Observaciones o notas del pedido
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
