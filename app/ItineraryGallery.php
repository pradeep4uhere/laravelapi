<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class ItineraryGallery extends Model
{
    public $timestamp = false;
    public function ItineraryGallery() {
        return $this->belongsTo(Itinerary::class);
    }


   
}
