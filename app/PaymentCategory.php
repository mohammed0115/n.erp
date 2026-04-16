<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class PaymentCategory extends Model
{
    //
    protected $guarded=[];

    public function role(){
        return $this->belongsTo(Role::class);
    }
}
