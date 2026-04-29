<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $password = env('TEST_USERS_PASSWORD', 'password');
        $emailDomain = env('TEST_USERS_EMAIL_DOMAIN', 'fabrica.local');
        $allPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();

        $profiles = [
            ['role' => 'admin', 'department' => 'Administrativo', 'name' => 'Administrador Teste'],
            ['role' => 'PCP', 'department' => 'Produção', 'name' => 'PCP Teste'],
            ['role' => 'operador', 'department' => 'Produção', 'name' => 'Operador Teste'],
            ['role' => 'qualidade_operacional', 'department' => 'Qualidade', 'name' => 'Qualidade Operacional Teste'],
            ['role' => 'qualidade_inspecao', 'department' => 'Qualidade', 'name' => 'Qualidade Inspeção Teste'],
            ['role' => 'qualidade_liberacao', 'department' => 'Qualidade', 'name' => 'Qualidade Liberação Teste'],
            ['role' => 'gestor', 'department' => 'Administrativo', 'name' => 'Gestor Teste'],
            ['role' => 'assistente', 'department' => 'Desenvolvimento', 'name' => 'Assistente Teste'],
            ['role' => 'estagiario', 'department' => 'Desenvolvimento', 'name' => 'Estagiário Teste'],
        ];

        foreach ($profiles as $profile) {
            $departamento = Departamento::query()->firstOrCreate([
                'nome' => $profile['department'],
            ]);

            $role = Role::query()->firstOrCreate([
                'name' => $profile['role'],
                'guard_name' => 'web',
            ]);

            $slug = Str::of($profile['role'])
                ->ascii()
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '.')
                ->trim('.')
                ->value();

            $email = "{$slug}@{$emailDomain}";

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $profile['name'],
                    'password' => Hash::make($password),
                    'departamento_id' => $departamento->id,
                    'status' => 'aprovado',
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$role->name]);

            if ($profile['role'] === 'admin') {
                $user->syncPermissions($allPermissions);
                continue;
            }

            $user->syncPermissions([]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
