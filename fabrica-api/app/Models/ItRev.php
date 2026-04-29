<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ItRev extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'nome',
        'url',
        'versao',
        'it_id_original',
    ];

    protected $dates = ['deleted_at'];

    public function versoes()
    {
        return $this->hasMany(ItRev::class, 'it_id_original');
    }

    public function original()
    {
        return $this->belongsTo(ItRev::class, 'it_id_original');
    }

    protected static function booted()
    {
        static::deleting(function ($itRev) {
            // Marca todas as versões como deletadas
            $itRev->versoes()->update(['deleted_at' => now()]);
        });
    }
}
