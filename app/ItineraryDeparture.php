<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class ItineraryDeparture extends Model
{
    public $timestamp = false;
    public function ItineraryDeparture() {
        return $this->belongsTo(Itinerary::class);
    }
    


   
}
