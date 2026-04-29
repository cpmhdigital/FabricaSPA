<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FluxoEtapa extends Model
{
    protected $table = 'fluxo_etapa';

    protected $fillable = [
        'fluxo_id',
        'etapa_id',
        'ordem',
        'tempo_estimado_minutos',
    ];

    public function fluxo()
    {
        return $this->belongsTo(Fluxo::class);
    }

    public function etapa()
    {
        return $this->belongsTo(Etapa::class);
    }
}
