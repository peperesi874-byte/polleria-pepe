<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipo', ['entrada','salida','ajuste']);
            // cantidad: en 'ajuste' representa el NUEVO stock; en entrada/salida, la cantidad movida
            $table->unsignedInteger('cantidad')->default(0);
            // delta: +N, -N o (nuevo - anterior) para 'ajuste'
            $table->integer('delta')->default(0);
            $table->string('motivo', 255)->nullable();
            $table->unsignedInteger('stock_resultante')->default(0);
            $table->timestamps();

            $table->index(['producto_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_movimientos');
    }
};
