<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialMediaAccount extends Model
{
    protected $fillable = [
        'platform',
        'username',
        'access_token',
        'access_token_secret',
        'page_id',
        'ig_user_id',   // <— TAMBAH ini untuk IG
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
