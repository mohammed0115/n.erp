<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends GlobalModel
{
    protected $fillable =[

        "name", "percentage", "is_active"
    ];
}
