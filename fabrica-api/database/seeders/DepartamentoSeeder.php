<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Departamento;

class DepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $departamentos = [
            'Produção',
            'Qualidade',
            'MKT',
            'Serviços Gerais',
            'Administrativo',
            'Desenvolvimento',
        ];
        foreach ($departamentos as $nome) {
            Departamento::firstOrCreate(['nome' => $nome]);
        }
    }
}
