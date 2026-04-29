<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maquina extends Model
{
    use HasFactory;

    protected $table = 'maquina';

    protected $fillable = ['departamento', 'codigo', 'tipo', 'modelo'];

    public function etapas()
    {
        return $this->belongsToMany(Etapa::class, 'etapa_maquina');
    }
}
