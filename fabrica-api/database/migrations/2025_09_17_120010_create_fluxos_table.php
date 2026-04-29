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
        Schema::create('fluxo', function (Blueprint $table) {
            $table->id();
            $table->string('nome_fluxo');
            $table->integer('tempo_estimado_dias')->nullable();
            $table->integer('tempo_estimado_dias_acelerado')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fluxo');
    }
};
