<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class ItineraryAddon extends Model
{
    public $timestamp = false;
    public function Itinerary() {
        return $this->belongsTo(Itinerary::class);
    }
    


   
}
