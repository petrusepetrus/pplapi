<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneType extends Model
{
    use HasFactory;
    protected $fillable = [
        'phone_type'
    ];

    public function phoneUser(){
        return $this->hasMany('App\Models\PhoneUser');
    }

    public function phonePhoneType(){
        return $this->hasMany(Pivot::class,'phone_type_id');
    }
}
