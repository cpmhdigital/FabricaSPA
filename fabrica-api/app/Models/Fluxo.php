<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fluxo extends Model
{
    use SoftDeletes;

    protected $table = 'fluxo';

    protected $fillable = [
        'nome_fluxo',
        'tempo_estimado_dias',
        'tempo_estimado_dias_acelerado',
    ];
    public function etapas()
    {
        return $this->belongsToMany(
            Etapa::class,
            'fluxo_etapa',
            'fluxo_id',
            'etapa_id'
        )
            ->withPivot(['ordem', 'tempo_estimado_minutos'])
            ->orderBy('fluxo_etapa.ordem') 
            ->withTimestamps();
    }

    public function itens()
    {
        return $this->hasMany(Itens::class, 'fluxo_id');
    }
}
