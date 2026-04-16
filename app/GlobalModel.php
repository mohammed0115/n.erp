<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class GlobalModel extends Model
{
    //
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if(session('organization_id')) {
                $model->organization_id = session('organization_id');
            }
            if (session('branch_id')){
                $model->branch_id = session('branch_id');
            }
//            $model->save();
        });

        static::addGlobalScope('branch', function (Builder $builder) {
            // $builder->where('branch_id',  '1'); //Session::get('branch_id')
            if(session('organization_id')) {
                $builder->where('organization_id', session('organization_id')); //Session::get('organization_id')
            }
            if (session('branch_id')){
                $builder->where('branch_id', session('branch_id')); //Session::get('organization_id')
            }
        });
    }
}
