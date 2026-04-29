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
        Schema::create('etapa_parametros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etapa_id')->constrained('etapa')->onDelete('cascade');
            $table->string('nome'); 
            $table->enum('tipo', ['texto', 'numero', 'simnao', 'data']);
            $table->enum('limite', ['nao', 'sim', 'produto'])->nullable();
            $table->integer('min')->nullable();
            $table->integer('max')->nullable();
            $table->boolean('obrigatorio')->default(false);
            $table->integer('ordem')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametros_etapa');
    }
};
