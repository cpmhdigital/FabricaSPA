<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Spatie\Activitylog\Models\Activity;

use App\Http\Controllers\Api\{
    AuthController,
    UserController,
    SessionController,
    VerificationController,
    DepartamentoController,
    PermissaoController,
    RoleController,
    RolePermissionController,
    SetorOpController,
    EtapaController,
    FluxoController,
    ItRevController,
    MaquinaController,
    ItensController,
    ItensComposicaoController,
    PedidoController,
    HistoricoProducaoController,
    PedidoParametroProducaoController,
    RegistroParadaController,
    ProducaoController,
    MateriaPrimaController,
    PedidoLightController,
    KanbanController,
    ConectaPedidosController,
    UserRoleController,
    MeController,
    PainelController,
    DooController
};

/**
 * Rate limit login
 */
RateLimiter::for('login', function (Request $request) {
    $email = (string) $request->email;

    return Limit::perMinute(5)
        ->by($email . $request->ip())
        ->response(fn() => response()->json([
            'message' => 'Muitas tentativas. Aguarde 5 minutos antes de tentar novamente.'
        ], 429));
});

/**
 * =========================
 * ROTAS PUBLICAS
 * =========================
 */
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/register', [UserController::class, 'store']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('/reset-password/{token}', function ($token, Request $request) {
        $email = $request->query('email');
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
        return redirect("{$frontendUrl}/new-password?token={$token}&email={$email}");
    })->name('password.reset');

    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
});

// Se isso for público, OK; se não for, mova para dentro do auth
Route::get('/departamento', [DepartamentoController::class, 'index']);
Route::get('/conecta/pedidos', [ConectaPedidosController::class, 'index']);

/**
 * =========================
 * ROTAS PROTEGIDAS (LOGADO)
 * =========================
 */
Route::middleware('auth:sanctum')->group(function () {

    /**
     * ME (unica rota /me) -> user + roles + permissions
     */

    Route::middleware('auth:sanctum')->get('/me', [MeController::class, 'show']);

    Route::get('/painel/cards', [PainelController::class, 'cards']);

    Route::patch('/usuarios/{user}/role', [UserRoleController::class, 'update']);

    /**
     * AUTH / PERFIL
     */
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/resend-confirmation', [AuthController::class, 'resendConfirmationEmail']);

    Route::prefix('perfil')->group(function () {
        Route::get('/', [UserController::class, 'perfil']);
        Route::post('/alterar-senha', [UserController::class, 'alterarSenha']);
    });

    /**
     * DEPARTAMENTOS / ROLES / PERMISSOES (se for admin-only, depois você aplica middleware de role/permission)
     */

    Route::get('/roles', [RoleController::class, 'index']);
    Route::apiResource('roles', RoleController::class)->except(['destroy']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);

    Route::get('/permissoes', [PermissaoController::class, 'index']);
    Route::apiResource('permissoes', PermissaoController::class);

    Route::get('/roles-permissions', [RolePermissionController::class, 'index']);
    Route::post('/roles/{role}/permissions', [RolePermissionController::class, 'assign']);
    Route::delete('/roles/{role}/permissions/{permission}', [RolePermissionController::class, 'revoke']);

    /**
     * USUARIOS / SESSOES
     */
    Route::get('/usuarios-online', [SessionController::class, 'usuariosOnline']);
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/lista', [UserController::class, 'usuarios']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });
    Route::get('/status-opcoes', [UserController::class, 'getOpcoesStatus']);

    /**
     * PEDIDOS
     */
    Route::prefix('pedidos')->group(function () {
        Route::get('/', [PedidoController::class, 'index']);
        Route::post('/', [PedidoController::class, 'store']);
        Route::post('/{pedido}/itens', [PedidoController::class, 'storeItens']);
        Route::get('/{pedido}', [PedidoController::class, 'show']);
        Route::put('/{pedido}', [PedidoController::class, 'update']);
        Route::delete('/{pedido}', [PedidoController::class, 'destroy']);
    });

    Route::get('/pedidos/light/{id}', [PedidoLightController::class, 'show']);

    /**
     * ITENS
     */
    Route::prefix('itens')->controller(ItensController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/tipo', 'indexTipo');

        Route::get('/pendentes', 'pendentes');
        Route::get('/busca', 'buscaSimples');

        Route::post('/', 'store');
        Route::get('/filtros', 'filtros');
        Route::get('/verificar-codigo', 'verificarCodigo');
        Route::get('/buscar', 'buscar');

        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::put('/{id}/finalizar', 'finalizar');
    });

    Route::prefix('itens-composicao')->controller(ItensComposicaoController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/buscar', 'buscar');
        Route::get('/autocomplete', 'autocomplete');
        Route::get('/{id}', 'show');
        Route::delete('/{id}', 'destroy');
    });

    /**
     * KANBAN / PRODUCAO
     */
    Route::get('/kanban', [KanbanController::class, 'index']);
    Route::prefix('producao')->group(function () {
        Route::get('/{pedidoId}', [ProducaoController::class, 'show']);
    });

    /**
     * HISTORICO PRODUCAO
     */
    Route::prefix('historico-producao')->group(function () {
        Route::get('/', [HistoricoProducaoController::class, 'index']);
        Route::post('/', [HistoricoProducaoController::class, 'store']);
        Route::get('/{id}', [HistoricoProducaoController::class, 'show']);
        Route::put('/{id}', [HistoricoProducaoController::class, 'update']);
        Route::delete('/{id}', [HistoricoProducaoController::class, 'destroy']);
    });

    /**
     * SETORES / ETAPAS / FLUXOS
     */
    Route::prefix('setores')->group(function () {
        Route::get('/', [SetorOpController::class, 'index']);
        Route::post('/', [SetorOpController::class, 'store']);
        Route::get('/{id}', [SetorOpController::class, 'show']);
        Route::put('/{id}', [SetorOpController::class, 'update']);
        Route::delete('/{id}', [SetorOpController::class, 'destroy']);
    });

    Route::prefix('etapas')->group(function () {
        Route::get('/', [EtapaController::class, 'index']);
        Route::post('/', [EtapaController::class, 'store']);
        Route::get('/{id}', [EtapaController::class, 'show']);
        Route::put('/{id}', [EtapaController::class, 'update']);
        Route::delete('/{id}', [EtapaController::class, 'destroy']);
    });

    Route::get('/setores/{id}/etapas', [EtapaController::class, 'etapasPorSetor']);
    Route::patch('/etapas/associar-setor', [EtapaController::class, 'associarSetor']);
    Route::patch('/etapas/{etapa}/desassociar-setor', [EtapaController::class, 'desassociarEtapa']);

    Route::prefix('fluxos')->group(function () {
        Route::get('/', [FluxoController::class, 'index']);
        Route::get('/{id}', [FluxoController::class, 'show']);
        Route::post('/', [FluxoController::class, 'store']);
        Route::put('/{id}', [FluxoController::class, 'update']);
        Route::delete('/{id}', [FluxoController::class, 'destroy']);
    });
    Route::get('/itens/{id}/fluxo', [FluxoController::class, 'showFluxoItem']);

    /**
     * PARAMETROS / PARADAS / MP / ITREV / MAQUINAS
     */
    Route::prefix('pedido-parametros-producao')->group(function () {
        Route::get('/', [PedidoParametroProducaoController::class, 'index']);
        Route::post('/', [PedidoParametroProducaoController::class, 'store']);
        Route::put('/{id}', [PedidoParametroProducaoController::class, 'update']);
        Route::delete('/{id}', [PedidoParametroProducaoController::class, 'destroy']);
    });

    Route::prefix('registro-paradas')->group(function () {
        Route::get('/', [RegistroParadaController::class, 'index']);
        Route::post('/', [RegistroParadaController::class, 'store']);
        Route::put('/{id}', [RegistroParadaController::class, 'update']);
        Route::delete('/{id}', [RegistroParadaController::class, 'destroy']);
    });

    Route::prefix('pedido-mps')->group(function () {
        Route::get('/buscar', [MateriaPrimaController::class, 'index']);
        Route::post('/', [MateriaPrimaController::class, 'store']);
        Route::get('/{id}', [MateriaPrimaController::class, 'show']);
        Route::put('/{id}', [MateriaPrimaController::class, 'update']);
        Route::delete('/{id}', [MateriaPrimaController::class, 'destroy']);
    });

    Route::prefix('itrev')->group(function () {
        Route::get('/', [ItRevController::class, 'index']);
        Route::get('/{id}/versoes', [ItRevController::class, 'versoes']);
        Route::post('/', [ItRevController::class, 'store']);
        Route::get('/{id}', [ItRevController::class, 'show']);
        Route::delete('/{id}', [ItRevController::class, 'destroy']);
    });

    Route::prefix('maquinas')->group(function () {
        Route::get('/', [MaquinaController::class, 'index']);
        Route::post('/', [MaquinaController::class, 'store']);
        Route::put('/{id}', [MaquinaController::class, 'update']);
        Route::delete('/{id}', [MaquinaController::class, 'destroy']);
        Route::get('/logs', [MaquinaController::class, 'logs']);
    });

    /**
     * DOO
     */
    Route::prefix('doo')->group(function () {
        Route::get('/health', [DooController::class, 'health'])
            ->middleware('permission:menu doo integracao');

        Route::get('/matrizes', [DooController::class, 'matrizes'])
            ->middleware('permission:menu doo matrizes');

        Route::post('/matrizes/sync', [DooController::class, 'syncMatrizes'])
            ->middleware('permission:menu doo integracao');
    });

    /**
     * LOGS (se isso for admin-only, depois você restringe por permission)
     */
    Route::get('/logs', function () {
        $logs = Activity::latest()
            ->with('causer')
            ->take(50)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'description' => $log->description,
                    'user' => $log->causer ? $log->causer->name : 'Sistema',
                    'user_id' => $log->causer_id,
                    'causer_type' => $log->causer_type,
                    'properties' => $log->properties,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json(['logs' => $logs]);
    });
});
