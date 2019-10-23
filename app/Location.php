<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class Location extends Model
{

     public function children() { 
        return $this->hasMany('App\Location', 'parent_id', 'id'); 
    }


}
