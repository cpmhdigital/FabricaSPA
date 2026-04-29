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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pedido')->unique();
            $table->string('lote')->nullable();
            $table->string('doutor')->nullable();
            $table->string('paciente')->nullable();
            $table->enum('tipo', ['Nacional', 'Internacional'])->default('Nacional');
            $table->boolean('taxa_extra')->default(false);

            // Data do pedido vindo da API
            $table->timestamp('data_pedido')->nullable();

            // Campos PCP
            $table->enum('status', ['aguardando', 'aprovado', 'arquivada'])->default('aguardando');
            $table->foreignId('responsavel_pcp_id')->nullable()->constrained('users');
            $table->timestamp('data_aprovacao_pcp')->nullable();
            $table->text('observacoes_pcp')->nullable();

            // Timestamps do Laravel para histórico de criação/atualização
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
