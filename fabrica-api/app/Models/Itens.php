<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itens extends Model
{
    use HasFactory;

    protected $table = 'itens';

    protected $fillable = [
        'codigo',
        'descricao',
        'anvisa',
        'tipo',
        'fluxo_id',
    ];

    // 🔹 Relação com os filhos (componentes ou subitens)
    public function filhos()
    {
        return $this->belongsToMany(
            Itens::class,
            'itens_composicao',
            'item_pai_id',
            'item_filho_id'
        );
    }

    public function componentes()
    {
        return $this->belongsToMany(
            Itens::class,
            'itens_composicao',
            'item_pai_id',
            'item_filho_id'
        )->where('itens_composicao.tipo', 'componente');
    }


    // 🔹 Relação com o pai (item que contém este)
    public function pai()
    {
        return $this->belongsToMany(
            Itens::class,
            'itens_composicao',
            'item_filho_id',
            'item_pai_id'
        );
    }
    public function fluxo()
    {
        return $this->belongsTo(Fluxo::class, 'fluxo_id');
    }
   public function itens()
    {
        return $this->hasMany(Itens::class, 'fluxo_id');
    }
 
    public static function carregarFilhosRecursivo($item, $nivel = 0, $maxNivel = 5)
    {
        $item->load('fluxo');

        if ($nivel < $maxNivel) {
            $item->filhos = $item->filhos()->with('fluxo')->get()
                ->map(fn($filho) => self::carregarFilhosRecursivo($filho, $nivel + 1, $maxNivel));
        }

        return $item;
    }

    public function composicoes()
    {
        return $this->hasMany(ItensComposicao::class, 'item_pai_id');
    }

    public function materiasPrimas()
    {
        return $this->belongsToMany(
            Itens::class,
            'itens_composicao',
            'item_pai_id',
            'item_filho_id'
        )->where('itens.tipo', 'materia_prima');
    }
}
