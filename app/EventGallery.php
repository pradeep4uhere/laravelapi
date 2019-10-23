<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class EventGallery extends Model
{
    public $timestamp = false;
    public function Event() {
        return $this->belongsTo(Event::class);
    }


   
}
