<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_item_componente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_item_id')
                  ->constrained('pedido_item')
                  ->onDelete('cascade');
            $table->foreignId('componente_id')
                  ->constrained('itens');
            $table->integer('quantidade')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_item_componente');
    }
};
