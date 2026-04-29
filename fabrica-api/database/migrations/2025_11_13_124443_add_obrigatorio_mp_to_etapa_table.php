<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etapa', function (Blueprint $table) {
            if (!Schema::hasColumn('etapa', 'obrigatorio_mp')) {
                $table->boolean('obrigatorio_mp')->default(false)->after('anexo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('etapa', function (Blueprint $table) {
            if (Schema::hasColumn('etapa', 'obrigatorio_mp')) {
                $table->dropColumn('obrigatorio_mp');
            }
        });
    }
};
