<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Tipo de entrega (mostrador o domicilio)
            if (!Schema::hasColumn('pedidos', 'tipo_entrega')) {
                $table->enum('tipo_entrega', ['mostrador', 'domicilio'])
                      ->default('mostrador')
                      ->after('estado');
            }

            // Datos de entrega (solo si domicilio)
            if (!Schema::hasColumn('pedidos', 'entrega_nombre')) {
                $table->string('entrega_nombre', 100)->nullable()->after('total');
            }
            if (!Schema::hasColumn('pedidos', 'entrega_telefono')) {
                $table->string('entrega_telefono', 20)->nullable()->after('entrega_nombre');
            }
            if (!Schema::hasColumn('pedidos', 'entrega_calle')) {
                $table->string('entrega_calle', 100)->nullable()->after('entrega_telefono');
            }
            if (!Schema::hasColumn('pedidos', 'entrega_numero')) {
                $table->string('entrega_numero', 20)->nullable()->after('entrega_calle');
            }
            if (!Schema::hasColumn('pedidos', 'entrega_colonia')) {
                $table->string('entrega_colonia', 80)->nullable()->after('entrega_numero');
            }
            if (!Schema::hasColumn('pedidos', 'entrega_municipio')) {
                $table->string('entrega_municipio', 80)->nullable()->after('entrega_colonia');
            }
            if (!Schema::hasColumn('pedidos', 'entrega_referencias')) {
                $table->string('entrega_referencias', 150)->nullable()->after('entrega_municipio');
            }

            // Motivo cancelación
            if (!Schema::hasColumn('pedidos', 'motivo_cancelacion')) {
                $table->string('motivo_cancelacion', 255)->nullable()->after('entrega_referencias');
            }

            // Repartidor asignado
            if (!Schema::hasColumn('pedidos', 'asignado_a')) {
                $table->foreignId('asignado_a')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('motivo_cancelacion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Si algún día quieres revertir, puedes eliminar los campos aquí.
            // Ejemplo:
            // $table->dropColumn([
            //     'tipo_entrega', 'entrega_nombre', 'entrega_telefono',
            //     'entrega_calle', 'entrega_numero', 'entrega_colonia',
            //     'entrega_municipio', 'entrega_referencias',
            //     'motivo_cancelacion', 'asignado_a'
            // ]);
        });
    }
};
