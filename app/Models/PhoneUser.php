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
        return $this->belongsTo(User::class);
    }

    public function phone(){
        return $this->belongsTo(Phone::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phone_type(){
        return $this->belongsTo(PhoneType::class,'phone_type_id');
    }
}
