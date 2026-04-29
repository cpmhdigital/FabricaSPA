<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SetorOp extends Model
{

    use SoftDeletes;
    protected $fillable = [
        'nome',
    ];

    // Relação 1:N - Um Setor pode ter várias Etapas
    public function etapas()
    {
        return $this->hasMany(Etapa::class);
    }
}
