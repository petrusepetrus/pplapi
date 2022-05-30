<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AddressUser extends Pivot
{
    protected $table='address_user';

    protected $fillable=[
        'user_id',
        'address_id',
        'address_type_id',
        'preferred_contact_address'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function address(){
        return $this->belongsTo(Address::class);
    }

    public function address_type(){
        return $this->belongsTo(AddressType::class,'address_type_id');
    }
}
