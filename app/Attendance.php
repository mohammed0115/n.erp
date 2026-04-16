<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends GlobalModel
{
    protected $fillable =[
        "date", "employee_id", "user_id",
        "checkin", "checkout", "status", "note"
    ];
}
