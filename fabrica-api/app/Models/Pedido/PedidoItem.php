<?php

namespace App\Models\Pedido;

use Illuminate\Database\Eloquent\Model;
use App\Models\Itens;
use App\Models\Pedido\PedidoItemUnidade;

class PedidoItem extends Model
{
    protected $table = 'pedido_item';

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'quantidade'
    ];

    public function produto()
    {
        return $this->belongsTo(Itens::class, 'produto_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function unidades()
    {
        return $this->hasMany(PedidoItemUnidade::class, 'pedido_item_id');
    }

    public function componentes()
    {
        return $this->hasMany(PedidoItemComponente::class, 'pedido_item_id');
    }

}
