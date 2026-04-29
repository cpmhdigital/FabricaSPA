<?php

namespace App\Models\Producao;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Etapa;
use App\Models\Pedido\PedidoItemUnidade;
use App\Models\Pedido\PedidoItem;
use App\Models\Pedido\Pedido;
use App\Models\User;

class HistoricoProducao extends Model
{
    use HasFactory;

    protected $table = 'historico_producao';

    protected $fillable = [
        'pedido_id',
        'pedido_item_id',
        'pedido_item_unidade_id',
        'etapa_id',
        'usuario_id',
        'acao',
        'tipo_decisao',
        'etapa_destino_id',
        'observacao',
        'data_hora',
    ];

    protected $casts = [
        'data_hora' => 'datetime',
    ];

    // Relacionamentos
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function pedidoItem()
    {
        return $this->belongsTo(PedidoItem::class);
    }

    public function unidade()
    {
        // unidade individual de produção
        return $this->belongsTo(PedidoItemUnidade::class, 'pedido_item_unidade_id');
    }

    public function etapa()
    {
        return $this->belongsTo(Etapa::class);
    }

    public function etapaDestino()
    {
        return $this->belongsTo(Etapa::class, 'etapa_destino_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
