<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class Country extends Model
{

    public function State() {
        return $this->hasMany(State::class);
    }

    public function User() {
        return $this->hasMany(User::class);
    }

}
