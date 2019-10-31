<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class ItineraryDayGallery extends Model
{
    public $timestamp = false;
    public function ItineraryDay() {
        return $this->belongsTo(ItineraryDay::class);
    }


   
}
