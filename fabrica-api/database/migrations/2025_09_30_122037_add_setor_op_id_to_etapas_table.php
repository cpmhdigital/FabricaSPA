<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSetorOpIdToEtapasTable extends Migration
{
    public function up()
    {
        Schema::table('etapa', function (Blueprint $table) {
            $table->foreignId('setor_op_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('etapa', function (Blueprint $table) {
            $table->dropColumn('setor_op_id');
        });
    }
}
