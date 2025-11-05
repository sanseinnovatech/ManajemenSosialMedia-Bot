<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostLog extends Model
{
    protected $fillable = ['account_id', 'platform', 'username', 'message', 'status', 'post_url'];
    public function account() {
        return $this->belongsTo(SocialMediaAccount::class);
    }
}

