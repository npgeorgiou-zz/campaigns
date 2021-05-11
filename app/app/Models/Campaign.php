<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model {
    protected $fillable = [
        'user_id',
        'channel',
        'name',
        'source',
        'target_url',
    ];

}
