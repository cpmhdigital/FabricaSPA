<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_revs', function (Blueprint $table) {
            $table->id();

            // Se for nova versão, aponta para a IT original
            $table->unsignedBigInteger('it_id_original')->nullable();

            $table->string('versao')->default('v1.0'); // versão
            $table->string('nome'); // Nome da IT
            $table->string('url'); // Caminho ou link do arquivo
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('it_id_original')
                  ->references('id')
                  ->on('it_revs')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_revs');
    }
};
