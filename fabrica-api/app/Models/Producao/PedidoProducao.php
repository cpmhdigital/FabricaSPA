<?php

namespace App\Models\Producao\Producao;

use Illuminate\Database\Eloquent\Model;

class PedidoProducao extends Model
{
    protected $fillable = [
        'numero_pedido',
        'item',
        'lote',
        'doutor',
        'paciente',
        'tipo',
        'taxa_extra',
    ];
}
