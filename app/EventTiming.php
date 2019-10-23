<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class EventTiming extends Model
{

    public function EventDetail() {
        return $this->belongsTo(EventDetail::class)->with('Theatre');
    }

   
    public function Event() {
        return $this->belongsTo(EventDetail::class)->with('Event');
    }

    public function Theatre() {
        return $this->belongsTo(Theatre::class)->with('EventSeat');
    }

    public function Price() {
        return $this->hasMany(Price::class)->with('SittingType');
    }


    public function EventDetailOnly() {
        return $this->belongsTo(EventDetail::class);
    }

    public function TempSeatBooking() {
        return $this->belongsTo(TempSeatBooking::class)->with('Theatre');
    }


}
