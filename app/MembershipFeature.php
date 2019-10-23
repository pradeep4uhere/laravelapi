<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class MembershipFeature extends Model
{


    public function MembershipPlan() {
        return $this->belongsTo(MembershipPlan::class);
    }

}
