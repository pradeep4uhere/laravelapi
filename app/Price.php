<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class Price extends Model
{

    public function EventTiming() {
        return $this->belongsTo(EventTiming::class)->with('SittingType');
    }

    public function SittingType() {
        return $this->belongsTo(SittingType::class);
    }

}
