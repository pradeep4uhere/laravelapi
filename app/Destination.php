<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class Destination extends Model
{

    public function DestinationGallery() {
        return $this->hasMany(DestinationGallery::class);
    }

}


