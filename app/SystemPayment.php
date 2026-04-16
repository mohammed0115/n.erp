<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemPayment extends Model
{
    //
    protected $guarded=[];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function category(){
        return $this->belongsTo(PaymentCategory::class , 'category_id' , 'id');
    }
}
