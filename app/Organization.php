<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mvdnbrk\EloquentExpirable\Expirable;

class Organization extends Model
{
    use Expirable;
    //
    protected $guarded=[];
}
