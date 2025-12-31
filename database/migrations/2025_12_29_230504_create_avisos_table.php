<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avisos', function (Blueprint $table) {
            $table->id();

            $table->string('titulo', 120);
            $table->text('texto');

            $table->string('tag', 30)->default('INFO');            // IMPORTANTE | TIP | META | INFO
            $table->string('color', 20)->default('slate');         // amber | blue | emerald | rose | slate
            $table->string('icono', 16)->nullable();               // emoji (💡, 🛠️, 🎯, etc.)

            $table->boolean('is_active')->default(true);           // publicado
            $table->boolean('pinned')->default(false);             // fijado

            $table->timestamp('starts_at')->nullable();            // desde cuándo aparece
            $table->timestamp('ends_at')->nullable();              // caduca

            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['is_active', 'pinned']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avisos');
    }
};
