<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait GlobalTrait {
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

//        if (!Auth::check()) {
//            static::addGlobalScope('branch', function (Builder $builder) {
//                // $builder->where('branch_id',  '1'); //Session::get('branch_id')
//                if(session('organization_id')) {
//                    $builder->where('organization_id', session('organization_id')); //Session::get('organization_id')
//                } else {
//                    $builder->whereNull('organization_id'); //Session::get('organization_id')
//                }
//                if (session('branch_id')){
//                    $builder->where('branch_id', session('branch_id')); //Session::get('organization_id')
//                } else {
//                    $builder->whereNull('branch_id'); //Session::get('organization_id')
//                }
//            });
//        }
//        else {
//            if(session('organization_id')) {
//                static::addGlobalScope('branch', function (Builder $builder) {
//                    // $builder->where('branch_id',  '1'); //Session::get('branch_id')
//                    if(session('organization_id')) {
//                        $builder->where('organization_id', session('organization_id')); //Session::get('organization_id')
//                    }
//                    if (session('branch_id')){
//                        $builder->where('branch_id', session('branch_id')); //Session::get('organization_id')
//                    }
//                });
//            }
//        }

    }
}