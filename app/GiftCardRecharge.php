<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GiftCardRecharge extends GlobalModel
{
    protected $table = 'gift_card_recharges';

    protected $fillable =[

        "gift_card_id", "amount", "user_id"
    ];
}
