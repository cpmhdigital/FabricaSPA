<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissaoController extends Controller
{
    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json($permission);
    }
    public function index()
    {
        return response()->json(['data' => Permission::all()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $perm = Permission::create($validated);

        return response()->json($perm, 201);
    }

    public function update(Request $request, Permission $permisso)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $permisso->update($validated);

        return response()->json($permisso);
    }

    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $permission->delete();

            return response()->json([
                'message' => 'Permissão excluída com sucesso'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao excluir a permissão',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
