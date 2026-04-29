<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'ip',
        'user_agent',
        'logged_in_at',
        'logged_out_at',
    ];

    protected $dates = ['logged_in_at', 'logged_out_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
