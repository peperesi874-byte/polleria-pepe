<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('notificaciones', 'leida')) {
                $table->boolean('leida')->default(false)->index()->after('meta');
            }
        });

        // Si existía una columna 'leido', copia valores a 'leida'
        if (Schema::hasColumn('notificaciones', 'leido')) {
            DB::statement('UPDATE notificaciones SET leida = leido WHERE leido IS NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            if (Schema::hasColumn('notificaciones', 'leido')) {
                $table->dropColumn('leido');
            }
        });
    }
};
