<?php

namespace App\Models\Pedido;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoItemComponenteUnidade extends Model
{
    use HasFactory;

    protected $table = 'pedido_item_componente_unidade';

    protected $fillable = [
        'pedido_item_componente_id',
        'unidade_codigo',
    ];

    // Relacionamento com pedido_item_componente
    public function pedidoItemComponente()
    {
        return $this->belongsTo(PedidoItemComponente::class, 'pedido_item_componente_id');
    }
}
