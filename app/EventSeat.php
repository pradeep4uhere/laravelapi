<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class EventSeat extends Model
{

    public function SittingType() {
        return $this->belongsTo(SittingType::class)->with('Theatre');
    }

    public function Theatre() {
        return $this->belongsTo(Theatre::class);
    }


}
