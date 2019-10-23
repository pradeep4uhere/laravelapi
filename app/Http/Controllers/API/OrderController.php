<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MasterController;
use Illuminate\Support\Facades\Hash;
use Auth;
use Session;
use App\State;
use App\City;
use App\StoreType;
use Log;
use App\Setting;
use App\Order;
use App\User;
use App\EventTiming;
use App\EventSeat;
use App\TempSeatBooking;



class OrderController extends MasterController
{
     

    public function prePaymentBooking(Request $request){
      	//Check if this user email address is already present into DB
    	$user = User::where('email','=',$request->get('email'))->get();
    	if($user->count()){
    		$userDetails = $user[0];
    	}else{
    		$userDetails = array();
    	}
    
    	$other = $request->get('code');
    	//Get All the Data into Foramte
    	$orderObj = new  Order();
    	if($userDetails){
    		$orderData['user_id']		= $userDetails->id;	
    	}else{
            try{
                $user = array();
                $password = '123456';
                $user['username']       = strstr($request->get('email'),true);
                $user['first_name']     = $request->get('fname');
                $user['last_name']      = $request->get('lname');
                $user['email']          = $request->get('email');
                $user['phone']          = $request->get('mobile');
                $user['street_address'] = $request->get('address1');
                $user['address_2']      = $request->get('address2');
                $user['postcode']       = $request->get('pincode');
                $user['status']         = 1;
                $user['created_at']     = date("Y-m-d H:i:s");
                $user['password']       = Hash::make($password);

                $id= User::create($user)->id;
              	$orderData['user_id']		= $id;
            } catch (Exception $e) {
                $responseArray['status'] = false;
                $responseArray['code'] = 500;
                $responseArray['message'] ="Somthing went wrong, plz try after sometime.";
                return $responseArray;
            }
    	}
    	$orderData['orderID']			= time();
    	$orderData['order_status_id']	= '1';
    	$orderData['email_address']		= $request->get('email');
    	$orderData['session']			= $request->get('user_id');
    	$orderData['order_date']		= date('Y-m-d H:i:s');
    	$orderData['ipaddress']			= $request->get('ip_address');
    	$orderData['subtotal']			= str_replace(',','',$other['other']['subTotal']);
    	$orderData['total_amount']		= str_replace(',','',$other['other']['total']);
    	if($other['other']['offerValue']!=''){
    		$orderData['is_offer_applied']	= '1';
    	}
    	$orderData['offer_id']			= decrypt($other['other']['oid']);
    	$orderData['offer_type']		= "";
    	$orderData['offer_code']		= "";
    	$orderData['offer_value']		= $other['other']['offerValue'];
    	$orderData['tax_amount']		= str_replace(',','',$other['other']['gstAmount']);
    	$orderData['shipping_fname']	= $request->get('fname');
    	$orderData['shipping_lname']	= $request->get('lname');
    	$orderData['shipping_address1']	= $request->get('address1');
    	$orderData['shipping_address2']	= $request->get('address2');
    	$orderData['shipping_state']	= $request->get('city');
    	$orderData['shipping_city']		= $request->get('state');
    	$orderData['shipping_pincode']	= $request->get('pincode');
    	$orderData['shipping_email']	= $request->get('email');
    	$orderData['shipping_mobile']	= $request->get('mobile');
    	$orderData['billing_fname']		= $request->get('bfname');
    	$orderData['billing_lname']		= $request->get('bfname');
    	$orderData['billing_address1']	= $request->get('baddress1');
    	$orderData['billing_address2']	= $request->get('baddress2');
    	$orderData['billing_state']		= $request->get('bcity');
    	$orderData['billing_city']		= $request->get('bstate');
    	$orderData['billing_pincode']	= $request->get('bpincode');
    	$orderData['billing_email']		= $request->get('bemail');
    	$orderData['billing_mobile']	= $request->get('user_id');
    	$orderData['created_at']		= date("Y-m-d H:i:s");
    	//print_r($orderData);die;

    	//check is this Order is already present 
        //Create User For this Order is User is not registred with us
        
    	
        $id = Order::create($orderData)->id;
    	if($id>0){
    			//Save All Event Timing and Seats for this order
    			$this->saveEventTimeAndSeat($request,$id);
    			$orderArr = Order::find($id);

    			$responseArray['status']  	= true;
		        $responseArray['code'] 	  	= 200;
	            $responseArray['message'] 	= "Order Creadted";
	            $responseArray['order'] 	= $orderArr;
                $responseArray['oid']       = encrypt($id);


    	}else{
        		$responseArray['status'] = false;
		        $responseArray['code'] = 500;
	            $responseArray['message'] ="Invalid Code.";
        	}
        return response()->json($responseArray);
    }



    private function saveEventTimeAndSeat($request,$orderId){
    	$other = $request->get('code');
    	$cartItem = $other['other']['cart'];
    	if(!empty($cartItem)){
    		foreach($cartItem as $item){
    			$eventTimingId = $item['attributes']['event_timing_id'];
    			$seatingTypeId = $item['attributes']['seat_type_id'];
    			$theatre_id = $this->getEventTiming($eventTimingId);
    			//GET ALL THE SEATS
    			if($theatre_id!=''){
    				$allSeats = $this->getTheaterSeats($theatre_id,$seatingTypeId);
    				if(!empty($allSeats)){
    					$rowCount = $this->bookEventTimingTempSeat($allSeats,$item['quantity'],$orderId,$eventTimingId);
    				}
    			}
    			
    		}	
    	}
    }



    /*
     * Seat Block for Current Order
     * This is temperory table, for store and block the seat for 5 min
     */
    private function bookEventTimingTempSeat($allSeats,$quantity,$orderId,$eventTimingId){
    	$count = 0;
    	if($quantity>0){
    		for($i=0;$i<$quantity;$i++){
    			$seat = $allSeats[$i];
    			$saveData['order_id']=$orderId;
    			$saveData['event_timing_id']=$eventTimingId;
    			$saveData['event_seat_id']=$seat['id'];
    			$saveData['booking_status_id']='3';
    			TempSeatBooking::create($saveData);
    			$count++;
    		}
    		return $count;
    	}
    	return $count;
    }









    private function getTheaterSeats($theatre_id,$seatingId){
    	$seatArr = array();
    	if($seatingId!='' && $theatre_id!=''){
    		$seatArr = EventSeat::where('theatre_id','=',$theatre_id)
    		->where('sitting_type_id','=',$seatingId)
    		->where('booking_status_id','=',2)
    		->get()->toArray();
    		return $seatArr;
    	}else{
    		return $seatArr;
    	}

    }

    private function getEventTiming($id){
    	$eventTime = EventTiming::find($id);
    	if(!empty($eventTime)){
    		return $eventTime['theatre_id'];
    	}else{
    		return NULL;
    	}
    }




}
