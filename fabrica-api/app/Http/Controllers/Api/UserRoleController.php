<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserRoleController extends Controller
{
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'string', Rule::in([
                'admin',
                'operador',
                'PCP',
                'qualidade_operacional',
                'qualidade_inspecao',
                'qualidade_liberacao',
                'gestor',
                'assistente',
                'estagiario',
            ])],
        ]);

        $user->syncRoles([$data['role']]);

        return response()->json([
            'message' => 'Role atualizada com sucesso.',
            'user_id' => $user->id,
            'roles' => $user->getRoleNames()->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
        ]);
    }
}
