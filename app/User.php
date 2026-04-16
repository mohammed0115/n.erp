<?php

namespace App;

use App\Traits\GlobalTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Mvdnbrk\EloquentExpirable\Expirable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;
    use GlobalTrait;
    use Expirable;

    protected $fillable = [
        'name', 'email', 'password',"phone","company_name", "role_id", "biller_id", "warehouse_id", "is_active", "is_deleted","organization_id","expires_at"
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function isActive()
    {
        return $this->is_active;
    }

    public function holiday() {
        return $this->hasMany('App\Holiday');
    }

    public function role(){
        return $this->belongsTo(Role::class , 'role_id' , 'id');
    }
}
