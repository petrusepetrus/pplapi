<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'address_line_1',
        'address_line_2',
        'town',
        'region',
        'post_code',
        'country_id',
    ];

    /**
     * One to Many relationship with user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this
            ->belongsToMany('App\Models\User')
            ->withPivot('address_type_id','preferred_contact_address');
    }
}
