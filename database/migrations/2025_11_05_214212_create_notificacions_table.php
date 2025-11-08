<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 80);
            $table->string('titulo', 120);
            $table->text('mensaje')->nullable();
            $table->json('meta')->nullable();
            $table->string('nivel', 20)->default('info');      // info|warning|critical
            $table->string('estado', 20)->default('nueva');    // nueva|enviada|fallida
            $table->timestamp('leida_at')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'user_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('notificaciones');
    }
};
