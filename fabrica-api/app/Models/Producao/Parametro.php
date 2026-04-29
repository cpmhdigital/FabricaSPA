<?php

namespace App\Models\Producao;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pedido\Pedido;
use App\Models\Pedido\PedidoItemUnidade;
use App\Models\Etapa;
use App\Models\EtapaParametro;
use App\Models\User;

class Parametro extends Model
{
    use HasFactory;

    protected $table = 'pedido_parametros_producao';

    protected $fillable = [
        'pedido_id',
        'item_id',
        'etapa_id',
        'pedido_item_unidade_id',
        'parametro_id',
        'valor',
        'usuario_id',
    ];

    //  Relacionamentos
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function unidade()
    {
        return $this->belongsTo(PedidoItemUnidade::class, 'pedido_item_unidade_id');
    }

    public function etapa()
    {
        return $this->belongsTo(Etapa::class);
    }

    public function parametro()
    {
        return $this->belongsTo(EtapaParametro::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
