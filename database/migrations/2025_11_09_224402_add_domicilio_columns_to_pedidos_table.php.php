<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // si no existen, agrégalos
            if (!Schema::hasColumn('pedidos', 'tipo_entrega')) {
                $table->string('tipo_entrega', 20)->default('mostrador'); // mostrador | domicilio
            }
            if (!Schema::hasColumn('pedidos', 'observaciones')) {
                $table->string('observaciones', 255)->nullable();
            }
            if (!Schema::hasColumn('pedidos', 'dom_nombre')) {
                $table->string('dom_nombre', 120)->nullable();
            }
            if (!Schema::hasColumn('pedidos', 'dom_telefono')) {
                $table->string('dom_telefono', 30)->nullable();
            }
            if (!Schema::hasColumn('pedidos', 'dom_direccion')) {
                $table->string('dom_direccion', 255)->nullable();
            }
            if (!Schema::hasColumn('pedidos', 'dom_colonia')) {
                $table->string('dom_colonia', 120)->nullable();
            }
            if (!Schema::hasColumn('pedidos', 'dom_ciudad')) {
                $table->string('dom_ciudad', 120)->nullable(); // «Tapachula, Chiapas»
            }
            if (!Schema::hasColumn('pedidos', 'dom_referencias')) {
                $table->string('dom_referencias', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $cols = ['dom_nombre','dom_telefono','dom_direccion','dom_colonia','dom_ciudad','dom_referencias'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('pedidos', $c)) $table->dropColumn($c);
            }
            // no quitamos tipo_entrega/observaciones por compatibilidad
        });
    }
};
