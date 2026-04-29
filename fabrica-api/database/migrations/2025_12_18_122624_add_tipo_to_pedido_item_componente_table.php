<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_item_componente', function (Blueprint $table) {
            $table->enum('tipo', ['normal', 'extra'])
                  ->default('normal')
                  ->after('quantidade');
        });

        // Garante consistência para registros antigos
        DB::table('pedido_item_componente')
            ->whereNull('tipo')
            ->update(['tipo' => 'normal']);
    }

    public function down(): void
    {
        Schema::table('pedido_item_componente', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
