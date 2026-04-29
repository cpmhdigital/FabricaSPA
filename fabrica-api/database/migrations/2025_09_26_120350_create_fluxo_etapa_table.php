<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fluxo_etapa', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('fluxo_id');
            $table->unsignedBigInteger('etapa_id');

            $table->integer('ordem')->default(1);
            $table->integer('tempo_estimado_minutos')->nullable();

            $table->timestamps();

            $table->foreign('fluxo_id')
                  ->references('id')->on('fluxo')
                  ->onDelete('cascade');

            $table->foreign('etapa_id')
                  ->references('id')->on('etapa')
                  ->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fluxo_etapa');
    }
};
