<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class Itinerary extends Model
{
    protected $table = 'itineraries';
    
    public function ItineraryDeparture() {
        return $this->hasMany(ItineraryDeparture::class);
    }

    public function ItineraryDay() {
        return $this->hasMany(ItineraryDay::class);
    }

}
