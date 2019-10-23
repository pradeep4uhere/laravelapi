<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class State extends Model
{

    public function City() {
        return $this->hasMany(City::class);
    }
    public function Country() {
        return $this->belongsTo(Country::class);
    }

}
