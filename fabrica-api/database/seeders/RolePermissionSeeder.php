<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // ============================================================
        // PRODUCAO - SETOR (operador)
        // ============================================================
        $permsSetorView = [
            'visualizar fila setor',
            'visualizar detalhes item setor',
        ];

        $permsSetorAction = [
            'operar producao setor',
            'registrar mp setor',
            'reprovar item setor',
            'upload producao setor',
            'check listas setor',
            'adicionar parametros setor',
        ];

        // ============================================================
        // MENUS (UI / FRONTEND)
        // ============================================================
        $permsMenu = [
            // Ordem de Produção
            'menu ordem producao',
            'menu painel producao',
            'menu criar pedido',
            'menu produtos',
            'menu its',
            'menu config producao',

            // Ordem de Manutenção
            'menu ordem manutencao',
            'menu painel manutencao',
            'menu nova om',

            // Ordem de Serviço
            'menu ordem servico',
            'menu painel os',
            'menu nova os',
            'menu setor os',

            // Manutenção Operacional
            'menu manutencao operacional',
            'menu painel manutencao operacional',
            'menu nova manutencao operacional',
            'menu registro maquinas',

            // DOO
            'menu doo',
            'menu doo matrizes',
            'menu doo integracao',

            // Geral
            'menu geral',
            'menu dashboard',
            'menu usuarios online',
            'menu lista usuarios',
            'documentacao api',
        ];

        // ============================================================
        // VISAO GLOBAL (PCP/Qualidade/admin)
        // ============================================================
        $permsGlobalView = [
            'visualizar ordens de producao',
            'visualizar pedidos aguardando producao',
            'visualizar dados do pedido completo',
            'visualizar producao completa do pedido',
            'visualizar progresso do pedido',
            'visualizar datas de entrega',
        ];

        // ============================================================
        // DOCUMENTOS OP/OS/OM (PCP)
        // ============================================================
        $permsDocsCriacao = [
            'criar op',
            'criar os',
            'criar om',
        ];

        $permsDocsGestao = [
            'editar ordem de producao',
            'modificar ordem de producao',
            'adicionar setores na os',
            'criar maquinas',
            'inserir produtos',
            'modificar status op',
            'modificar status os',
            'modificar status om',
        ];

        $permsDocsChecklistRelatorios = [
            'criar checklist op',
            'criar checklist om',
            'ver relatorios op',
            'ver relatorios os',
            'ver relatorios om',
        ];

        // ============================================================
        // QUALIDADE (acoes)
        // ============================================================
        $permsQualidadeBase = [
            'inspecionar op',
            'retornar etapas',
            'reprovar item qualidade',
            'modificar it',
            'mudar upload',
            'ver reprovados por semestre',
        ];

        $permsQualidadeLiberacao = [
            'liberar esterilizacao',
            'liberar estoque',
        ];

        // ============================================================
        // ADMIN (admin)
        // ============================================================
        $permsAdmin = [
            'gerenciar usuarios',
            'aprovar solicitacoes de usuarios',
            'ver usuarios online',
        ];

        // ============================================================
        // REGISTRAR TODAS AS PERMISSOES
        // ============================================================
        $all = array_values(array_unique(array_merge(
            $permsSetorView,
            $permsSetorAction,
            $permsGlobalView,
            $permsDocsCriacao,
            $permsDocsGestao,
            $permsDocsChecklistRelatorios,
            $permsQualidadeBase,
            $permsQualidadeLiberacao,
            $permsAdmin,
            $permsMenu
        )));

        foreach ($all as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => $guard,
            ]);
        }

        // Helper: pega apenas permissoes existentes (por guard)
        $getPerms = function (array $names) use ($guard) {
            $names = array_values(array_unique($names));

            return Permission::query()
                ->where('guard_name', $guard)
                ->whereIn('name', $names)
                ->get();
        };

        // ============================================================
        // ROLES
        // ============================================================
        $operador        = Role::firstOrCreate(['name' => 'operador', 'guard_name' => $guard]);
        $admin           = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $pcp             = Role::firstOrCreate(['name' => 'PCP', 'guard_name' => $guard]);

        $qualOperacional = Role::firstOrCreate(['name' => 'qualidade_operacional', 'guard_name' => $guard]);
        $qualInspecao    = Role::firstOrCreate(['name' => 'qualidade_inspecao', 'guard_name' => $guard]);
        $qualLiberacao   = Role::firstOrCreate(['name' => 'qualidade_liberacao', 'guard_name' => $guard]);

        // ============================================================
        // ATRIBUICOES
        // ============================================================

        // Operador: setor + menus mínimos (se quiser que ele veja UI)
        $operador->syncPermissions($getPerms(array_merge(
            $permsSetorView,
            $permsSetorAction,
            [
                // menus mínimos que você quer que operador enxergue
                'menu geral',
                'menu dashboard',
                // se operador precisa ver documentação, habilite:
                // 'documentacao api',
            ]
        )));

        // PCP: visão global + docs + MENUS
        $pcp->syncPermissions($getPerms(array_merge(
            $permsGlobalView,
            $permsDocsCriacao,
            $permsDocsGestao,
            $permsDocsChecklistRelatorios,
            $permsMenu
        )));

        // Qualidade Operacional: global + setor + ações + MENUS
        $qualOperacional->syncPermissions($getPerms(array_merge(
            $permsGlobalView,
            $permsSetorView,
            $permsSetorAction,
            [
                'inspecionar op',
                'modificar it',
                'mudar upload',
            ],
            $permsMenu
        )));

        // Qualidade Inspeção: global + ações + MENUS
        $qualInspecao->syncPermissions($getPerms(array_merge(
            $permsGlobalView,
            [
                'inspecionar op',
                'retornar etapas',
                'reprovar item qualidade',
                'ver reprovados por semestre',
            ],
            $permsMenu
        )));

        // Qualidade Liberação: global + liberação + MENUS
        $qualLiberacao->syncPermissions($getPerms(array_merge(
            $permsGlobalView,
            $permsQualidadeLiberacao,
            [
                'retornar etapas',
            ],
            $permsMenu
        )));

        // Admin: tudo (inclui menus)
        $adminAll = array_values(array_unique(array_merge(
            $permsAdmin,
            $permsGlobalView,
            $permsDocsCriacao,
            $permsDocsGestao,
            $permsDocsChecklistRelatorios,
            $permsQualidadeBase,
            $permsQualidadeLiberacao,
            $permsSetorView,
            $permsSetorAction,
            $permsMenu
        )));

        $admin->syncPermissions($getPerms($adminAll));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
