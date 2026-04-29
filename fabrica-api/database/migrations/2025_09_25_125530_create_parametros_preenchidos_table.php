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
        Schema::create('parametros_preenchidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etapa_id')->constrained('etapa')->onDelete('cascade');
            $table->foreignId('parametro_id')->constrained('etapa_parametros')->onDelete('cascade');
            $table->text('valor');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametros_preenchidos');
    }
};
