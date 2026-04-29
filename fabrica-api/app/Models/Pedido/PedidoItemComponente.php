<?php

namespace App\Models\Pedido;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Itens;

class PedidoItemComponente extends Model
{
    use HasFactory;

    protected $table = 'pedido_item_componente';

    protected $fillable = [
        'pedido_item_id',
        'componente_id',
        'quantidade',
        'status',
    ];

    // Relacionamento com pedido_item
    public function pedidoItem()
    {
        return $this->belongsTo(PedidoItem::class, 'pedido_item_id');
    }

    // Relacionamento com item (componente)
    public function componente()
    {
        return $this->belongsTo(Itens::class, 'componente_id');
    }

    // Relacionamento com unidades do componente
    public function unidades()
    {
        return $this->hasMany(PedidoItemComponenteUnidade::class, 'pedido_item_componente_id');
    }
}
