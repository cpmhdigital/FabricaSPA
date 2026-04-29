<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtapaParametro extends Model
{
    protected $table = 'etapa_parametros';

    protected $fillable = [
        'etapa_id',
        'nome',
        'tipo',
        'limite',
        'min',
        'max',
        'obrigatorio',
        'ordem'
    ];

    // Relação inversa com Etapa
    public function etapa()
    {
        return $this->belongsTo(Etapa::class, 'etapa_id', 'id');
    }
}
