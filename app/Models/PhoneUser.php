<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PhoneUser extends Pivot
{
    protected $table='phone_user';

    protected $fillable=[
        'user_id',
        'phone_id',
        'phone_type_id',
        'preferred_contact_number'
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function phone(){
        return $this->belongsTo(Phone::class,'phone_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phoneType(){
        return $this->belongsTo(PhoneType::class,'phone_type_id');
    }
}
