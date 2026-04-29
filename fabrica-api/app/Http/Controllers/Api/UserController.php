<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;


class UserController extends Controller
{
    public function __construct()
    {
        // Protege todas as rotas com Sanctum
        $this->middleware('auth:sanctum')->except(['store']);

        // Permissões baseadas no Spatie
        /*  $this->middleware('permission:ver usuários')->only(['index', 'show']);
        $this->middleware('permission:editar usuários')->only('update');
        $this->middleware('permission:desativar usuários')->only('destroy'); */
    }

    /**
     * Listar todos os usuários com departamento
     */
    public function index()
    {
        $users = User::with('departamento', 'roles')->get();

        return response()->json([
            'message' => 'Lista de usuários',
            'users' => $users,
        ]);
    }

    //usado para listar todos os usuarios 
    public function usuarios()
    {
        $usuarios = \DB::table('users as u')
            ->leftJoin('departamento as d', 'u.departamento_id', '=', 'd.id')
            ->leftJoin('user_sessions as us', 'us.user_id', '=', 'u.id')
            ->select(
                'u.id',
                'u.name',
                'u.email',
                'd.nome as departamento_nome',

                // Último login
                \DB::raw('(SELECT logged_in_at FROM user_sessions
                      WHERE user_id = u.id
                      ORDER BY logged_in_at DESC
                      LIMIT 1) as last_login'),

                // Último logout
                \DB::raw('(SELECT logged_out_at FROM user_sessions
                      WHERE user_id = u.id
                      ORDER BY logged_out_at DESC
                      LIMIT 1) as last_logout'),

                // Último IP
                \DB::raw('(SELECT ip FROM user_sessions
                      WHERE user_id = u.id
                      ORDER BY logged_in_at DESC
                      LIMIT 1) as last_ip')
            )
            ->groupBy('u.id', 'u.name', 'u.email', 'd.nome')
            ->get();

        return response()->json([
            "message" => "Lista de usuários",
            "users" => $usuarios,
        ]);
    }


    /**
     * Criar novo usuário
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'departamento_id' => 'nullable|exists:departamento,id',
            'status' => 'nullable|in:aguardando,aprovado,inativo',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id',
            'permissoes' => 'nullable|array',
            'permissoes.*' => 'integer|exists:permissions,id',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome pode ter no máximo :max caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.unique' => 'Este e-mail já está em uso.',

            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo :min caracteres.',

            'departamento_id.exists' => 'O departamento selecionado não é válido.',

            'status.in' => 'O status informado não é válido.',

            'roles.*.exists' => 'Algum dos papéis informados é inválido.',
            'permissoes.*.exists' => 'Alguma das permissões informadas é inválida.',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        $user->sendEmailVerificationNotification();

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (isset($data['permissoes'])) {
            $perms = Permission::whereIn('id', $data['permissoes'])->pluck('name');
            $user->syncPermissions($perms);
        }

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ])
            ->log("Novo Usuário(a) Criado - '{$user->name}'");

        return response()->json([
            'message' => 'Usuário criado com sucesso. Verifique seu e-mail.',
            'user' => $user,
        ], 201);
    }

    /** Obter opções de status para usuários */
    public function getOpcoesStatus()
    {
        return response()->json([
            'status' => ['aguardando', 'aprovado', 'inativo']
        ]);
    }


    /**
     * Mostrar detalhes de um usuário
     */
    public function show($id)
    {
        $user = User::with(['departamento', 'roles', 'permissions'])->findOrFail($id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'departamento_id' => $user->departamento_id,
            'departamento' => $user->departamento,

            'role_id' => $user->roles->count() ? $user->roles->first()->id : null,
            'role_nome' => $user->roles->count() ? $user->roles->first()->name : null,

            'roles' => $user->roles->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
            ]),

            'permissoes_diretas' => $user->getDirectPermissions()->map(fn($p) => [
                'id' => $p->id,
                'nome' => $p->name,
                'descricao' => $p->description ?? null,
            ]),
            'permissoes_via_role' => $user->getPermissionsViaRoles()->map(fn($p) => [
                'id' => $p->id,
                'nome' => $p->name,
                'descricao' => $p->description ?? null,
            ]),
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
        ]);
    }

    public function perfil()
    {
        $auth = Auth::user();
        return response()->json([
            'id' => $auth->id,
            'name' => $auth->name,
            'email' => $auth->email,
            'permissoes' => $auth->permissions->pluck('name'),
        ]);
    }

    /**
     * Atualizar usuário
     */
    public function update(Request $request, $id)
    {
        $auth = Auth::user();
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|min:6',
            'departamento_id' => 'nullable|exists:departamento,id',
            'status' => 'nullable|in:aguardando,aprovado,inativo',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id',
            'permissoes' => 'nullable|array',
            'permissoes.*' => 'integer|exists:permissions,id',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $old = $user->replicate();

        $olddepartamento = optional($user->departamento)->nome;

        $oldRoles = $user->roles->pluck('name')->sort()->values()->toArray();

        $oldPermissions = $user->getDirectPermissions()->pluck('name')->sort()->values()->toArray();

        $user->update($data);

        $changes = collect($user->getChanges())->except(['departamento_id', 'updated_at'])->toArray();


        $newdepartamento = optional($user->fresh()->departamento)->nome;

        if ($olddepartamento !== $newdepartamento) {
            $changes['departamento'] = [
                'antes' => $olddepartamento,
                'depois' => $newdepartamento
            ];
        }

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
            $newRoles = $user->roles->pluck('name')->sort()->values()->toArray();

            if ($oldRoles !== $newRoles) {
                $changes['roles'] = [
                    'antes' => $oldRoles,
                    'depois' => $newRoles
                ];
            }
        }

        if (isset($data['permissoes'])) {
            $perms = Permission::whereIn('id', $data['permissoes'])->pluck('name')->sort()->values()->toArray();
            $user->syncPermissions($perms);

            $newPermissions = $user->getDirectPermissions()->pluck('name')->sort()->values()->toArray();

            if ($oldPermissions !== $newPermissions) {
                $changes['permissoes'] = [
                    'antes' => $oldPermissions,
                    'depois' => $newPermissions
                ];
            }
        }

        activity()
            ->causedBy($auth)
            ->performedOn($user)
            ->withProperties([
                'alteracoes' => $changes,
                'antes' => $old->only(array_keys($changes)),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'atualizado_por' => $auth->name,
            ])
            ->log("Usuário(a) '{$user->name}' foi atualizado por '{$auth->name}'");

        return response()->json([
            'message' => 'Usuário(a) atualizado com sucesso',
            'user' => $user,
        ]);
    }


    public function usuariosOnline()
    {
        $usuariosOnline = User::with('departamento')
            ->whereHas('sessions', function ($query) {
                $query->whereNull('logged_out_at')
                    ->where('logged_in_at', '>=', now()->subMinutes(60));
            })->get();

        // Formatar para enviar o nome do departamento
        $usuariosOnline = $usuariosOnline->map(function ($u) {
            return [
                'id' => $u->id,
                'nome' => $u->name,
                'email' => $u->email,
                'departamento_id' => $u->departamento_id,
                'departamento_nome' => $u->departamento->nome ?? 'Sem Departamento',
                'status' => $u->status,
                'ip' => $u->sessions->last()?->ip,
                'logged_in_at' => $u->sessions->last()?->logged_in_at,
            ];
        });

        return response()->json($usuariosOnline);
    }



    /*
    public function destroy($id)
    {
        $auth = Auth::user();

          if (!$auth->can('desativar usuários')) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        $user = User::findOrFail($id);
        $oldStatus = $user->status;
        $user->status = 'inativo';
        $user->save();

        activity()
            ->causedBy($auth)
            ->performedOn($user)
            ->withProperties([
                'antes' => ['status' => $oldStatus],
                'depois' => ['status' => $user->status],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ])
            ->log("Usuário(a) '{$user->name}' foi desativado.");


        return response()->json(['message' => 'Usuário(a) desativado com sucesso.']);
    } */

    public function alterarSenha(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'senha_atual' => 'required|string',
            'nova_senha' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->senha_atual, $user->password)) {
            return response()->json(['message' => 'Senha atual incorreta'], 422);
        }

        $user->password = Hash::make($request->nova_senha);
        $user->save();


        activity()
            ->causedBy($user)
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ])
            ->log("Usuário(a) '{$user->name}' alterou a senha.");

        return response()->json(['message' => 'Senha alterada com sucesso!']);
    }
}
