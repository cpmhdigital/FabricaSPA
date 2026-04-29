<?php

namespace App\Models\Pedido;

use Illuminate\Database\Eloquent\Model;
use App\Models\Producao\HistoricoProducao;

class PedidoItemUnidade extends Model
{
    protected $table = 'pedido_item_unidade';

    protected $fillable = ['pedido_item_id', 'unidade_codigo'];

    public function item()
    {
        return $this->belongsTo(PedidoItem::class, 'pedido_item_id');
    }

    public function etapas()
    {
        return $this->hasMany(PedidoItemStatus::class, 'pedido_item_unidade_id');
    }

    public function etapaAtual()
    {
        // Supondo que cada unidade tem muitos históricos
        return $this->hasOne(HistoricoProducao::class, 'pedido_item_unidade_id')
            ->latest('data_hora')
            ->with('etapa'); // se quiser trazer a etapa relacionada
    }

    public function historicos()
    {
        return $this->hasMany(HistoricoProducao::class, 'pedido_item_unidade_id');
    }
    // Retorna apenas o último historico
    public function ultimoHistorico()
    {
        return $this->hasOne(HistoricoProducao::class, 'pedido_item_unidade_id')
            ->latest('data_hora');
    }
}
