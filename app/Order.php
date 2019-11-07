<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{


  /**
     * The attributes that are mass assignable.
     *	
     * @var array
     */
    protected $fillable = [
        'user_id',
        'orderID',
        'order_type',
        'order_status_id', 
        'email_address', 
        'session', 
        'order_date', 
        'ipaddress', 
        'subtotal',
        'total_amount',
        'offerValue',
        'is_offer_applied',
        'offer_id',
        'offer_type',
        'offer_code',
        'offer_value',
        'tax_amount',
        'shipping_fname',
        'shipping_lname',
        'shipping_address1',
        'shipping_address2',
        'shipping_state',
        'shipping_city',
        'shipping_pincode',
        'shipping_email',
        'shipping_mobile',
        'billing_fname',
        'billing_lname',
        'billing_address1',
        'billing_address2',
        'billing_state',
        'billing_city',
        'billing_pincode',
        'billing_email',
        'billing_mobile', 
    ];

     public function OrderStatus() {
        return $this->belongsTo(OrderStatus::class);
    }


     public function User() {
        return $this->belongsTo(User::class);
    }

    public function TempSeatBooking() {
        return $this->hasMany(TempSeatBooking::class)->with('EventTiming','EventSeat');
    }
    
    public function ItineraryBooking() {
        return $this->hasMany(ItineraryBooking::class)->with('Itinerary','ItineraryDeparture');
    }

    





}
