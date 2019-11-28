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
use App\ItineraryBooking;
use App\MembershipPlanOrder;
use App\MembershipPlan;



class OrderController extends MasterController
{
	
	public function membershipBooking(Request $request){
		//Check if this user email address is already present into DB
		$id = $request->get('id');
		$uid = $request->get('uid');
		$type = $request->get('type');
		$user = User::where('email','=',$request->get('email'))->get();
	  if($user->count()){
		  $userDetails = $user[0];
	  }else{
			$userDetails = User::find($uid);
		}
		
		//Get Membership Details
		$MembershipPlan = MembershipPlan::find($id);
		if($type==1){
			$totalamount = $MembershipPlan['monthly_price'];
		}else if($type==2){
			$totalamount = $MembershipPlan['quarterly_price'];
		}else if($type==3){
			$totalamount = $MembershipPlan['yearly_price'];
		}else{
			$totalamount = $MembershipPlan['monthly_price'];
		}
		
		
	  //Get All the Data into Foramte
	  //$orderObj = new  Order();
	  $orderData['user_id']		= $userDetails->id;	
	  $orderData['orderID']			= time();
	  $orderData['order_type']		= '3';
	  $orderData['order_status_id']	= '1';
	  $orderData['email_address']		= $request->get('email');
	  $orderData['session']				= $request->get('user_id');
	  $orderData['order_date']		= date('Y-m-d H:i:s');
	  $orderData['ipaddress']			= $request->get('ip_address');
	  $orderData['subtotal']			= $totalamount;;
	  $orderData['total_amount']	= $totalamount;
	  $orderData['is_offer_applied']	= '0';
	  $orderData['offer_type']		= "";
	  $orderData['offer_code']		= "";
	  $orderData['offer_value']		= "0.00";
	  $orderData['tax_amount']		= "0.00";
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
				$orderArr = Order::find($id);
			  if($this->saveMembershipPlanOrder($request,$id)){
					$responseArray['message'] 	= "Membership Order Completed.";
				}else{
					$responseArray['message'] 	= "Membership Order recived. we will sent you confirmation as soon as possible";
				}
			  $responseArray['status']  	= true;
			  $responseArray['code'] 	  	= 200;
			  
			  $responseArray['order'] 	= $orderArr;
			  $responseArray['oid']       = encrypt($id);
	  }else{
			  $responseArray['status'] = false;
			  $responseArray['code'] = 500;
			  $responseArray['message'] ="Invalid Code.";
		  }
	  return response()->json($responseArray);
  }


	
	//Save Membership Plan Order
	private function saveMembershipPlanOrder($request,$id){
		$type = $request->get('type');
		//Get Membership Details
		$MembershipPlan = MembershipPlan::find($request->get('id'));
		if($type==1){
			$typeStr = 'Monthly';
			$paidAmount = $MembershipPlan['monthly_price'];
		}else if($type==2){
			$typeStr = 'Quaterly';
			$paidAmount = $MembershipPlan['quarterly_price'];
		}else if($type==3){
			$typeStr = 'Yearly';
			$paidAmount = $MembershipPlan['yearly_price'];
		}else{
			$typeStr = 'Monthly';
			$paidAmount = $MembershipPlan['monthly_price'];
		}


		$memObj = array();
		$memObj['user_id'] = $request->get('user_id');
		$memObj['order_id'] = $id;
		$memObj['membership_plan_id'] = $request->get('id');
		$memObj['order_date'] = date("Y-m-d H:i:s");
		$memObj['start_date'] = date("Y-m-d H:i:s");
		$memObj['end_date'] = date("Y-m-d H:i:s",strtotime('+30 days',time()));
		$memObj['plan_type'] = $typeStr;
		$memObj['paid_amount'] = $paidAmount;
		
		$memObj['status'] = 1;
		$memObj['created_at'] = date("Y-m-d H:i:s");
		$mid = MembershipPlanOrder::create($memObj)->id;
		//save Membership For User
		if($mid>0){
			$user = User::find($request->get('user_id'));
			$user->membership_plan_order_id =$mid;
			if($user->save()){
					return true;
			}
		}else{
			return false;
		}
}



	public function prePaymentExpBooking(Request $request){
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
	  $orderData['order_type']		= '2';
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
	  $orderData['offer_id']		= decrypt($other['other']['oid']);
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
			  $this->saveItineraryBooking($request,$id);
			  $orderArr = Order::find($id);
			  $responseArray['status']  	= true;
			  $responseArray['code'] 	  	= 200;
			  $responseArray['message'] 	= "Order Creadted";
			  $responseArray['order'] 	= $orderArr;
			  $responseArray['oid']       = encrypt($id);
			  \Cart::session($request->get('user_id'))->clear();
	  }else{
			  $responseArray['status'] = false;
			  $responseArray['code'] = 500;
			  $responseArray['message'] ="Invalid Code.";
		  }
	  return response()->json($responseArray);
  }

  
  private function saveItineraryBooking($request,$orderId){
	  $other = $request->get('code');
	  $cartItem = $other['other']['cart'];
	  if(!empty($cartItem)){
		  foreach($cartItem as $item){
			  $eventTimingId = $item['attributes']['itinerary_id'];
			  $seatingTypeId = $item['attributes']['itinerary_departures_id'];
			  $saveData['order_id']=$orderId;
			  $saveData['itinerary_id']=$item['attributes']['itinerary_id'];
			  $saveData['itinerary_departure_id']=$item['attributes']['itinerary_departures_id'];;
			  $saveData['booking_status_id']='3';
			  ItineraryBooking::create($saveData);	
			  $saveData=array();
		  }	
	  }
  }



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
				if($request->get('user_id')){
					$userID = $request->get('user_id');
					$userArr= User::where('id','=',$userID)->get();
					if(count($userArr)>0){
						$orderData['user_id']		= $userID;
					}else{
						$orderData['user_id']		= $userDetails->id;
					}	
				}else{
					$orderData['user_id']		= $userDetails->id;	
				}
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
			$orderData['order_type']		= '1';
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
				\Cart::session($request->get('user_id'))->clear();


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
	


	public function getLastOrderList(Request $request){
		$setting = Setting::all();
		$orderIdStr = $request->get('oid');
		$orderStrNew = str_replace("oid=",'',$orderIdStr);
		$responseArray = array();
		if($orderStrNew!=''){
			$orderId = decrypt($orderStrNew);
			if(is_numeric($orderId)){
				$orderDetails = Order::find($orderId)->toArray();
				if(!empty($orderDetails)){
					$orderDetailsArr = Order::with('ItineraryBooking')->find($orderId)->toArray();
					$responseArray['status']  	= true;
		        	$responseArray['code'] 	  	= 200;
	            	$responseArray['message'] 	= "Order Recived";
					$responseArray['order'] 	= $orderDetailsArr;
					$responseArray['setting'] 	= $setting;
					

				}else{
					$responseArray['status'] = false;
					$responseArray['code'] = 500;
					$responseArray['message'] ="Invalid Request.";
				}
			}else{
        		$responseArray['status'] = false;
		        $responseArray['code'] = 500;
	            $responseArray['message'] ="Invalid Request.";
        	}
		}else{
			$responseArray['status'] = false;
			$responseArray['code'] = 500;
			$responseArray['message'] ="Invalid Request.";
		}
		return response()->json($responseArray);
	}
	
}
