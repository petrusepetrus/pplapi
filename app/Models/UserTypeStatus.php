<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserTypeStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type_status'
    ];

    public function user(){
        return $this->hasMany('App\Models\User')
            ->using('App\Models\UserUserType')
            ->withPivot('user_type_status_id')
            ->withTimestamps();
    }
    public function userUserType(){
        return $this->hasMany('App\Models\UserUserType','user_type_status_id');
    }

    public function userType(){
        return $this->belongsToMany('App\Models\UserType')
            ->using('App\Models\UserUserType')
            ->withPivot('user_type_status_id')
            ->withTimestamps();
    }
}
