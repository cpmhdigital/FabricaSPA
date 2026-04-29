<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_producao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos');
            $table->foreignId('pedido_item_id')->constrained('pedido_item');
            $table->foreignId('pedido_item_unidade_id')->constrained('pedido_item_unidade');
            $table->foreignId('etapa_id')->constrained('etapa');
            $table->foreignId('usuario_id')->constrained('users');
            $table->enum('acao', ['INICIO', 'PAUSA', 'FINALIZACAO', 'REPROVACAO', 'DECISAO']);
            $table->enum('tipo_decisao', ['RETRABALHO', 'REFUGO'])->nullable();
            $table->foreignId('etapa_destino_id')->nullable()->constrained('etapa');
            $table->text('observacao')->nullable();
            $table->timestamp('data_hora')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_producao');
    }
};
