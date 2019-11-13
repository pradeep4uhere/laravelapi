<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;



    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 
        'password',
        'username',
        'first_name',
        'last_name',
        'phone',
        'street_address',
        'address_2',
        'postcode',
        'status'

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password' 
    ];

    public function Order() {
        return $this->hasMany(Order::class);
    }

    public function State() {
        return $this->belongsTo(State::class);
    }

    public function City() {
        return $this->belongsTo(City::class);
    }

    public function Country() {
        return $this->belongsTo(Country::class);
    }




}
