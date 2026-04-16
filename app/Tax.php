<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tax extends GlobalModel
{
    protected $fillable =[
        "name", "rate", "is_active"
    ];

    public function product()
    {
    	return $this->hasMany('App/Product');

    }
}
