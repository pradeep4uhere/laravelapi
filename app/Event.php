<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class Event extends Model
{
    protected $table = 'events';

    public function EventDetail() {
        return $this->hasMany(EventDetail::class)->with('Country','State','City','Language','EventTiming','Event');
    }

    public function EventGallery() {
        return $this->hasMany(EventGallery::class);
    }

    
}
