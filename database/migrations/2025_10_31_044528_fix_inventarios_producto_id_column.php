<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Agrega la nueva columna si no existe
        if (!Schema::hasColumn('inventarios', 'producto_id')) {
            Schema::table('inventarios', function (Blueprint $t) {
                $t->unsignedBigInteger('producto_id')->nullable()->after('id');
            });
        }

        // 2. Copia los datos desde id_producto a producto_id
        if (Schema::hasColumn('inventarios', 'id_producto') && Schema::hasColumn('inventarios', 'producto_id')) {
            DB::statement('UPDATE inventarios SET producto_id = id_producto WHERE producto_id IS NULL');
        }

        // 3. Cambia a NOT NULL, crea FK y unique
        Schema::table('inventarios', function (Blueprint $t) {
            $t->unsignedBigInteger('producto_id')->nullable(false)->change();
            $t->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
            $t->unique('producto_id');
        });

        // 4. Elimina la columna antigua si existe
        if (Schema::hasColumn('inventarios', 'id_producto')) {
            Schema::table('inventarios', function (Blueprint $t) {
                $t->dropColumn('id_producto');
            });
        }
    }

    public function down(): void
    {
        // Revertir: volver a tener id_producto
        Schema::table('inventarios', function (Blueprint $t) {
            if (!Schema::hasColumn('inventarios', 'id_producto')) {
                $t->unsignedBigInteger('id_producto')->nullable()->after('id');
            }
        });

        if (Schema::hasColumn('inventarios', 'producto_id') && Schema::hasColumn('inventarios', 'id_producto')) {
            DB::statement('UPDATE inventarios SET id_producto = producto_id WHERE id_producto IS NULL');
        }

        Schema::table('inventarios', function (Blueprint $t) {
            try { $t->dropUnique(['producto_id']); } catch (\Throwable $e) {}
            try { $t->dropForeign(['producto_id']); } catch (\Throwable $e) {}
            $t->dropColumn('producto_id');
        });
    }
};
