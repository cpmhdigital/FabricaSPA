<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    public function assign(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);
        $role->permissions()->syncWithoutDetaching($request->permission_id);
        return response()->json(['message' => 'Permissão atribuída com sucesso.']);
    }

    public function revoke($roleId, $permissionId)
    {
        $role = Role::findOrFail($roleId);
        $role->permissions()->detach($permissionId);

        return response()->json(['message' => 'Permissão removida com sucesso.']);
    }

    public function destroy(Role $role)
    {
        $role->permissions()->detach();
        $role->delete();

        return response()->json(['message' => 'Role e permissões removidas com sucesso']);
    }
}
