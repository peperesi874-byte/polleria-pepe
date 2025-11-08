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
        Schema::table('configuraciones', function (Blueprint $table) {
            // Solo agregar si no existe aún
            if (!Schema::hasColumn('configuraciones', 'zona_cobertura')) {
                $table->string('zona_cobertura')->default('Tapachula')->after('horario_cierre');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            if (Schema::hasColumn('configuraciones', 'zona_cobertura')) {
                $table->dropColumn('zona_cobertura');
            }
        });
    }
};
