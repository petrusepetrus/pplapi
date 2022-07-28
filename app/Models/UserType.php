<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserType extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type'
    ];

    public function user(){
        return $this->hasMany('App\Models\User')
            //->using('App\Models\UserUserType')
            ->withPivot('user_type_status_id');
    }

    public function userUserType(){
        return $this->hasMany(Pivot::class,'user_type_id');
    }

    public function userTypeStatus()
    {
        return $this->hasOne('App\Models\UserTypeStatus')
            ->using('App\Models\userUserType')
            ->withPivot('user_type_status_id');
    }
}
