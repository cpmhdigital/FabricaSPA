<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_item_componente_unidade', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_item_componente_id')
                  ->constrained('pedido_item_componente')
                  ->onDelete('cascade');
            $table->string('unidade_codigo'); // ex: 2/1, 2/2
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_item_componente_unidade');
    }
};
