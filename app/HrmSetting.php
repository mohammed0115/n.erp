<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HrmSetting extends GlobalModel
{
    protected $fillable =[
        "checkin", "checkout"
    ];
}
