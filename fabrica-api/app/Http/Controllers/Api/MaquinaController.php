<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maquina;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MaquinaController extends Controller
{
    public function index()
    {
        return Maquina::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
        ]);


        $maquina = Maquina::create([
            'codigo' => $validated['codigo'],
            'departamento' => $validated['departamento'],
            'tipo' => $validated['tipo'],
            'modelo' => $validated['modelo'],
        ]);


        return response()->json($maquina, 201);
    }


    public function update(Request $request, $id)
    {
        $maquina = Maquina::findOrFail($id);

        $validated = $request->validate([
            'departamento' => 'string',
            'codigo' => 'string',
            'tipo' => 'string',
            'modelo' => 'string'
        ]);

        $maquina->update($validated);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($maquina)
            ->withProperties([
                'alteracoes' => $validated,
                'ip' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ])
            ->log("Máquina {$maquina->codigo} atualizada");

        return response()->json($maquina);
    }

    public function destroy($id)
    {
        $maquina = Maquina::findOrFail($id);
        $maquina->delete();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($maquina)
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ])
            ->log("Maquina {$maquina->codigo} excluída");

        return response()->json(null, 204);
    }

    public function logs()
    {
        $logs = Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'description' => $log->description,
                    'properties' => $log->properties,
                    'created_at' => $log->created_at,
                    'causer' => $log->causer ? $log->causer->name : 'Sistema',
                ];
            });

        return response()->json($logs);
    }
}
