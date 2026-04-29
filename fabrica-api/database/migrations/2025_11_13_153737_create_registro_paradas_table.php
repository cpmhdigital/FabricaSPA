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
        Schema::create('registro_paradas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('itens')->onDelete('cascade');
            $table->foreignId('pedido_item_unidade_id')->constrained('pedido_item_unidade')->onDelete('cascade');

            $table->foreignId('etapa_id')->nullable()->constrained('etapa')->nullOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');

            $table->enum('motivo', ['almoco', 'lanche', 'fim_expediente', 'outro'])->default('outro');

            $table->text('observacao')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_paradas');
    }
};
