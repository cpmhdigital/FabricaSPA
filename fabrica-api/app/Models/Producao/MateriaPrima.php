<?php

namespace App\Models\Producao;

use Illuminate\Database\Eloquent\Model;
use App\Models\Itens;
use App\Models\Pedido\Pedido;
use App\Models\Etapa;

class MateriaPrima extends Model
{
    protected $table = 'pedido_materias_primas';

    protected $fillable = [
        'pedido_id',
        'pedido_item_unidade_id',
        'etapa_id',
        'materia_prima_id',
        'valor',
        'unidade',
        'lote',
        'usuario_id'
    ];

    public function item()
    {
        return $this->belongsTo(Itens::class, 'materia_prima_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function etapa()
    {
        return $this->belongsTo(Etapa::class, 'etapa_id');
    }
}
