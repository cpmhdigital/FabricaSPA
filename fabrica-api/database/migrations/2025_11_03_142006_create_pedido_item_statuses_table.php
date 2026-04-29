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
        Schema::create('pedido_item_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_item_unidade_id')->constrained('pedido_item_unidade')->onDelete('cascade');
            $table->foreignId('etapa_id')->constrained('etapa');
            $table->foreignId('colaborador_id')->constrained('users');
            $table->enum('status', ['pendente', 'andamento', 'pausado', 'finalizado', 'reprovado', 'retrabalho', 'refugo'])->default('pendente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_item_status');
    }
};
