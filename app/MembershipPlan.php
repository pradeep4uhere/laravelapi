<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class MembershipPlan extends Model
{

    public function MembershipFeature() {
        return $this->hasMany(MembershipFeature::class);
    }

}
