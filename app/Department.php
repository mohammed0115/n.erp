<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends GlobalModel
{
    protected $fillable =[
        "name", "is_active"
    ];
}
