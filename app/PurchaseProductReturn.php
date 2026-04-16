<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseProductReturn extends GlobalModel
{
    protected $table = 'purchase_product_return';
    protected $fillable =[
        "return_id", "product_id", "variant_id", "qty", "purchase_unit_id", "net_unit_cost", "discount", "tax_rate", "tax", "total"
    ];
}
