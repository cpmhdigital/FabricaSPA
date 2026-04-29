<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departamento', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('departamento')->insert([
            ['nome' => 'Produção', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Qualidade', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Estoque', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Manutenção', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Desenvolvimento', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Marketing', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Administração', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departamento');
    }
};
