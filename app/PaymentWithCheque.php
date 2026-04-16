<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentWithCheque extends GlobalModel
{
    protected $table = 'payment_with_cheque';

    protected $fillable =[

        "payment_id", "cheque_no"
    ];
}
