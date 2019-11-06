<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class ItineraryBooking extends Model
{


  /**
     * The attributes that are mass assignable.
     *	
     * @var array
     */
    protected $fillable = [
        'itinerary_id',
        'itinerary_departure_id', 
        'booking_status_id', 
        'order_id', 
    ];

    public function Itinerary() {
        return $this->belongsTo(Itinerary::class);
    }


     public function ItineraryDeparture() {
        return $this->belongsTo(ItineraryDeparture::class);
    }


}
