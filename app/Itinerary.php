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
        return $this->hasMany(ItineraryDay::class)->with('ItineraryDayGallery');
    }

    public function ItineraryGallery() {
        return $this->hasMany(ItineraryGallery::class);
    }

    public function ValidItineraryDeparture() {
        return $this->hasMany(ItineraryDeparture::class)->where('status','=', 1)->where('start_date', '>', \DB::raw('NOW()'))->orderBy('price','ASC');
    }

    
    public function ItineraryAddon() {
        return $this->hasMany(ItineraryAddon::class)->where('type','=', 1);
    }

    public function ItineraryTermsAndConditions() {
        return $this->hasMany(ItineraryAddon::class)->where('type','=', 2);;
    }

    



}
