<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id();

            // FK al pedido (al borrar un pedido, se borran sus items)
            $table->foreignId('pedido_id')
                  ->constrained('pedidos')
                  ->cascadeOnDelete();

            // FK al producto (no es cascade delete para no borrar históricos si borras productos)
            $table->foreignId('producto_id')
                  ->constrained('productos');

            $table->unsignedInteger('cantidad');            // pzas/kilos/unidad
            $table->decimal('precio_unitario', 10, 2);      // precio al momento
            $table->decimal('subtotal', 10, 2);             // cantidad * precio_unitario
            $table->string('notas', 255)->nullable();       // opcional

            $table->timestamps();

            // Evita el mismo producto duplicado en el mismo pedido
            $table->unique(['pedido_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_items');
    }
};

