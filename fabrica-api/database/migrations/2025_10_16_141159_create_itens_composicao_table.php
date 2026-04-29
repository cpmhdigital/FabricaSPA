<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItensComposicaoTable extends Migration
{
    public function up()
    {
        Schema::create('itens_composicao', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('item_pai_id');
            $table->unsignedBigInteger('item_filho_id');
            $table->string('tipo')->nullable();

            $table->timestamps();

            $table->foreign('item_pai_id')->references('id')->on('itens')->onDelete('cascade');
            $table->foreign('item_filho_id')->references('id')->on('itens')->onDelete('cascade');


            $table->unique(['item_pai_id', 'item_filho_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('itens_composicao');
    }
}
