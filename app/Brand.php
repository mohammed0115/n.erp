<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brand extends GlobalModel
{
    protected $fillable =[

        "title", "image", "is_active"
    ];

    public function product()
    {
    	return $this->hasMany('App/Product');

    }
}
