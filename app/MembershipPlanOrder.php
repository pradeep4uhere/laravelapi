<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
class MembershipPlanOrder extends Model
{

     /**
     * The attributes that are mass assignable.
     *	
     * @var array
     */
    protected $fillable = [
        'user_id',
        'order_id',
        'membership_plan_id',
        'order_date', 
        'start_date', 
        'end_date', 
        'status', 
        'plan_type',
        'paid_amount'
    ];
    public function MembershipPlan() {
        return $this->belongsTo(MembershipPlan::class)->with('MembershipFeature');
    }

    public function Order() {
        return $this->belongsTo(Order::class);
    }

    public function User() {
        return $this->belongsTo(User::class);
    }

}
