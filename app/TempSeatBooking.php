<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class TempSeatBooking extends Model
{


  /**
     * The attributes that are mass assignable.
     *	
     * @var array
     */
    protected $fillable = [
        'event_timing_id',
        'event_seat_id', 
        'booking_status_id', 
        'order_id', 
    ];

    public function EventTiming() {
        return $this->belongsTo(EventTiming::class);
    }


     public function EventSeat() {
        return $this->belongsTo(EventSeat::class);
    }


}
