<?php

namespace App\Models\Pedido;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Pedido\PedidoItem;
use App\Models\Etapa;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'numero_pedido',
        'lote',
        'doutor',
        'paciente',
        'tipo',
        'taxa_extra',
        'data_pedido',
        'status',
        'responsavel_pcp_id',
        'data_aprovacao_pcp',
        'observacoes_pcp',
    ];

    public function responsavelPcp(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'responsavel_pcp_id');
    }

    public function pedidoItens()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    public function etapaAtual()
    {
        return $this->belongsTo(Etapa::class, 'etapa_atual_id');
    }

    public function producaoHistorico()
    {
        return $this->hasMany(\App\Models\Producao\HistoricoProducao::class, 'pedido_id')
            ->orderBy('data_hora', 'asc');
    }

    public function itens()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }
}
