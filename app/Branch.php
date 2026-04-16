<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    //
    protected $fillable = [
        'name',
        'domain',
        'manager_id',
        'expired_at',
    ];
}
