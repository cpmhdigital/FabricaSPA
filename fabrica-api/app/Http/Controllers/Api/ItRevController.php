<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ItRev;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ItRevController extends Controller
{
    public function index()
    {
        return ItRev::all();
    }

    public function show($id, Request $request)
    {
        $it = ItRev::findOrFail($id);
        $idOriginal = $it->it_id_original ?? $it->id;
        $itOriginal = ItRev::find($idOriginal);

        $todasAsVersoes = ItRev::where('id', $idOriginal)
            ->orWhere('it_id_original', $idOriginal)
            ->orderBy('created_at', 'asc')
            ->get();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($it)
            ->withProperties([
                'ip' => $request->ip(),
            ])
            ->log("IT/REV '{$it->nome}' ({$it->versao}) visualizada por " . Auth::user()->name);

        return response()->json([
            'original' => $itOriginal,
            'versoes' => $todasAsVersoes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'url' => 'required|url',
            'it_id_original' => 'nullable|exists:it_revs,id',
        ]);

        if (empty($validated['it_id_original'])) {
            $validated['versao'] = 'v1.0';
        } else {
            $ultimaVersao = ItRev::where('it_id_original', $validated['it_id_original'])
                ->orWhere('id', $validated['it_id_original'])
                ->orderByDesc('created_at')
                ->first();

            $versaoAntiga = $ultimaVersao?->versao ?? 'v1.0';
            preg_match('/v(\d+)(?:\.(\d+))?/', $versaoAntiga, $matches);

            $major = (int)($matches[1] ?? 1);
            $minor = (int)($matches[2] ?? 0);

            $mudouUrl = $ultimaVersao && $ultimaVersao->url !== $validated['url'];

            if ($mudouUrl) {
                $validated['versao'] = 'v' . ($major + 1) . '.0';
            } else {
                $validated['versao'] = 'v' . $major . '.' . ($minor + 1);
            }
        }

        $itRev = ItRev::create($validated);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($itRev)
            ->withProperties([
                'ip' => $request->ip(),
            ])
            ->log("IT/REV - '{$itRev->nome}' ({$itRev->versao}) criada por " . Auth::user()->name);

        return response()->json($itRev, 201);
    }

    public function versoes($id)
    {
        $it = ItRev::findOrFail($id);
        $versoes = $it->versoes()->orderBy('created_at')->get();
        return response()->json($versoes);
    }

    public function update(Request $request, $id)
    {
        $itRev = ItRev::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'url' => 'required|url'
        ]);

        $itRev->update($validated);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($itRev)
            ->withProperties([
                'ip' => $request->ip(),
            ])
            ->log("IT/REV - '{$itRev->nome}' atualizada por " . Auth::user()->name);

        return response()->json($itRev);
    }

    public function destroy($id)
    {
        // Pega a IT selecionada
        $itRev = ItRev::findOrFail($id);

        // Determina o id original do grupo
        $idOriginal = $itRev->it_id_original ?? $itRev->id;

        // Marca todas as ITs do grupo (original + versões) como deletadas
        ItRev::where('id', $idOriginal)
            ->orWhere('it_id_original', $idOriginal)
            ->update(['deleted_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'IT/REV e todas as versões deletadas'
        ]);
    }
}
