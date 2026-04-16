<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentWithGiftCard extends GlobalModel
{
    protected $table = 'payment_with_gift_card';
    protected $fillable =[
        "payment_id", "gift_card_id"
    ];
}
