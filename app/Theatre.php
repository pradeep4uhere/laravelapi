<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class Theatre extends Model
{

    public function EventTiming() {
        return $this->hasMany(EventTiming::class);
    }

    public function EventDetail() {
        return $this->hasMany(EventDetail::class);
    }


    public function EventSeat() {
        return $this->hasMany(EventSeat::class);
    }

    public function City() {
        return $this->belongsTo(City::class);
    }

}
