<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checklist extends Model
{
    use SoftDeletes;

    protected $table = 'checklists';

    protected $fillable = [
        'etapa_id',
        'nome',
        'tipo',
    ];

   public function etapa()
    {
        return $this->belongsTo(Etapa::class, 'etapa_id');
    }
}
