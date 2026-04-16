<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentWithPaypal extends GlobalModel
{
    protected $table = 'payment_with_paypal';
    protected $fillable =[
        "payment_id", "transaction_id"
    ];
}
