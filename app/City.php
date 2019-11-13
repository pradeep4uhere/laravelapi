<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class City extends Model
{
    public function State() {
        return $this->belongsTo(State::class);
    }


    public function Theatre() {
        return $this->hasMany(Theatre::class);
    }

    public function User() {
        return $this->hasMany(User::class);
    }
}
