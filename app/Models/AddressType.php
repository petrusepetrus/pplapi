<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AddressType extends Model
{
    use HasFactory;
    protected $fillable = [
        'address_type'
    ];

    public function addressUser(){
        return $this->hasMany('App\Models\AddressUser');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addressAddressType(){
        return $this->hasMany(Pivot::class,'address_type_id');
    }
}
