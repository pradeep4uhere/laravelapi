<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class DestinationGallery extends Model
{
    public $timestamp = false;
    public function Destination() {
        return $this->belongsTo(Destination::class);
    }


   
}
