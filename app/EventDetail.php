<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class EventDetail extends Model
{
    protected $table = 'event_details';

   

    public function State() {
        return $this->belongsTo(State::class);
    }

    public function Country() {
        return $this->belongsTo(Country::class);
    }

    public function City() {
        return $this->belongsTo(City::class);
    }

    public function Language() {
        return $this->belongsTo(Language::class);
    }

    public function Event() {
        return $this->belongsTo(Event::class);
    }

    public function EventTiming() {
        return $this->hasMany(EventTiming::class)->with('Theatre','Price');
    }

    public function EventWithImage() {
        return $this->belongsTo(Event::class)->with('EventGallery');
    }

}
