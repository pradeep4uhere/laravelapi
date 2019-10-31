<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class ItineraryDay extends Model
{
    public $timestamp = false;
    public function Itinerary() {
        return $this->belongsTo(Itinerary::class);
    }

    public function ItineraryDayGallery() {
        return $this->hasMany(ItineraryDayGallery::class);
    }


   
}
