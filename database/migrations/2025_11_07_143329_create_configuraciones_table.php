<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Crea la tabla configuraciones con valores por defecto.
     */
    public function up(): void
    {
       Schema::create('configuraciones', function (Blueprint $table) {
    $table->id();
    $table->time('horario_apertura')->nullable();
    $table->time('horario_cierre')->nullable();
    $table->string('zona_cobertura')->nullable();
    $table->integer('stock_umbral')->default(5);
    $table->timestamps();
});


        // Inserta la fila inicial (id=1)
        DB::table('configuraciones')->insert([
            'id'                 => 1,
            'horario_apertura'   => '09:00:00',
            'horario_cierre'     => '18:00:00',
            'zona_cobertura'     => 'Tapachula',
            'umbral_stock_bajo'  => 5,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }

    /**
     * Elimina la tabla si se hace rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
