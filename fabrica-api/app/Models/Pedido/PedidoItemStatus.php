<?php

namespace App\Models\Pedido;

use Illuminate\Database\Eloquent\Model; 
use App\Models\Pedido\PedidoItemUnidade;

class PedidoItemStatus extends Model
{
    protected $fillable = [
        'pedido_item_unidade_id',
        'etapa_id',
        'colaborador_id',
        'status'
    ];

    public function unidade() {
        return $this->belongsTo(PedidoItemUnidade::class, 'pedido_item_unidade_id');
    }
}
