<?php

namespace App\Models\Producao;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pedido\Pedido;
use App\Models\Pedido\PedidoItem;
use App\Models\Pedido\PedidoItemUnidade;
use App\Models\User;
use App\Models\Etapa;

class RegistroParada extends Model
{
    protected $table = 'registro_paradas';

    protected $fillable = [
        'pedido_id',
        'item_id',
        'pedido_item_unidade_id',
        'etapa_id',
        'usuario_id',
        'motivo',
        'observacao'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function pedidoItem()
    {
        return $this->belongsTo(PedidoItem::class, 'pedido_item_id');
    }

    public function pedidoItemUnidade()
    {
        return $this->belongsTo(PedidoItemUnidade::class, 'pedido_item_unidade_id');
    }

    public function etapa()
    {
        return $this->belongsTo(Etapa::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
