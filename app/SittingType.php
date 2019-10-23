<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class SittingType extends Model
{

    public function Price() {
        return $this->hasMany(Price::class);
    }

}


