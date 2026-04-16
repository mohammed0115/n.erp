<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends GlobalModel
{
    protected $fillable = ["name", "code", "exchange_rate"];
}
