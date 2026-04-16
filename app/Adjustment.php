<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Adjustment extends GlobalModel
{
    protected $fillable =[
        "reference_no", "warehouse_id", "document", "total_qty", "item",
         "note"
    ];
}
