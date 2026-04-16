<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deposit extends GlobalModel
{
    protected $fillable =[
        "amount", "customer_id", "user_id", "note"
    ];
}
