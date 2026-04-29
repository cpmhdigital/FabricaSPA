<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Etapa extends Model
{
    use SoftDeletes;

    protected $table = 'etapa';

    protected $fillable = [
        'nome_etapa',
        'colaboracao_multipla',
        'anexo',
        'obrigatorio_mp',
        'setor_op_id',
    ];

    // Relação N-N com IT/REV
    public function itRevs()
    {
        return $this->belongsToMany(ItRev::class, 'etapa_itrev', 'etapa_id', 'it_rev_id');
    }

    // Relação N-N com Máquinas
    public function maquinas()
    {
        return $this->belongsToMany(Maquina::class, 'etapa_maquina', 'etapa_id', 'maquina_id');
    }

    // Relação N-N
    public function fluxos()
    {
        return $this->belongsToMany(Fluxo::class, 'fluxo_etapa')
            ->withPivot('ordem', 'tempo_estimado_minutos')
            ->withTimestamps();
    }

    // Relação 1-N com Parâmetros
    public function parametros()
    {
        return $this->hasMany(EtapaParametro::class, 'etapa_id', 'id');
    }

    // Relações 1:N com Checklist
    public function checklistsPre()
    {
        return $this->hasMany(\App\Models\Checklist::class, 'etapa_id')
            ->where('tipo', 'pre');
    }

    public function checklistsPos()
    {
        return $this->hasMany(\App\Models\Checklist::class, 'etapa_id')
            ->where('tipo', 'pos');
    }



    // Relação 1:N - Uma Etapa pertence a um único Setor
    public function setor()
    {
        return $this->belongsTo(SetorOp::class);
    }
}
