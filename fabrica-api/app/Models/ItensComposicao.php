<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItensComposicao extends Model
{
    protected $table = 'itens_composicao';

    protected $fillable = [
        'item_pai_id',
        'item_filho_id',
        'tipo', // opcional, mas útil (ex: 'componente', 'materia_prima')
    ];

    // Relacionamento com o item pai
    public function itemPai()
    {
        return $this->belongsTo(Itens::class, 'item_pai_id');
    }

    public function itemFilho()
    {
        return $this->belongsTo(Itens::class, 'item_filho_id');
    }
}
