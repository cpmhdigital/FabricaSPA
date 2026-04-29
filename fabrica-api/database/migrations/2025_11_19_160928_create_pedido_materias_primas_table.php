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
        Schema::create('pedido_materias_primas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('pedido_item_unidade_id')->constrained('pedido_item_unidade')->onDelete('cascade');
            $table->foreignId('etapa_id')->constrained('etapa')->onDelete('cascade');
            $table->foreignId('materia_prima_id')->constrained('itens');
            $table->string('valor')->nullable();
            $table->string('unidade')->nullable();
            $table->string('lote')->nullable();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_materias_primas');
    }
};
