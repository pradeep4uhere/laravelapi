<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MasterController;
use Illuminate\Contracts\Encryption\DecryptException;
use Auth;
use Session;
use Log;
use App\User;
use App\Page;
use App\Event;
use App\Setting;
use App\StoreType;
use App\ReviewVideo;
use App\EventTiming;
use App\SittingType;
use App\EventDetail;
use App\MembershipPlan;
use App\MembershipFeature;
use App\Offer;
use App\Tax;
use App\Itinerary;
use App\ItineraryDay;
use App\ItineraryDayGallery;
use App\ItineraryGallery;
use App\ItineraryDeparture;
class CartController extends MasterController
{
     
     private function getEventImage($event_gallery){
    	if(!empty($event_gallery)){
    		$count=1;
    		foreach ($event_gallery as $key => $value) {
    			if($count==1){
    				return env('APP_URL').'/storage/app/public/event/'.$value['image'];
    			}
    		}
    	}
	}
	

	

	public function updateExpItemFromCartList(Request $request){
    	if(!empty($request->all())){
        		$userId		=  $request->get('user_id');
        		$itemId		=  $request->get('itemId');
        		$quantity	=  $request->get('quantity');

        		// you may also want to update a product's quantity
        		$u = \Cart::session($userId)->update($itemId, array(
				 'quantity' => array
				 	(
				      'relative' => false,
				      'value' => $quantity
				  	)
					)
        		);
				if($u){
        			$responseArray['status'] = true;
	        		$responseArray['code'] = 200;
	        		$responseArray['message'] = "Item qunatity updated in cart.";
	        	}else{
	        		$responseArray['status'] = false;
		        	$responseArray['code'] = 500;
		        	$responseArray['message'] = "Somthing Went wrong, Please try after somtime";	
	        	}
        	}else{
				$responseArray['status'] = false;
	        	$responseArray['code'] = 500;
	        	$responseArray['message'] = "Invalid Request";
			}
			return response()->json($responseArray);
	}


    public function updateItemFromCartList(Request $request){
    	if(!empty($request->all())){
        		$userId		=  $request->get('user_id');
        		$itemId		=  $request->get('itemId');
        		$quantity	=  $request->get('quantity'); 

        		// you may also want to update a product's quantity
        		$u = \Cart::session($userId)->update($itemId, array(
				 'quantity' => array
				 	(
				      'relative' => false,
				      'value' => $quantity
				  	)
					)
				);
				if($u){
        			$responseArray['status'] = true;
	        		$responseArray['code'] = 200;
	        		$responseArray['message'] = "Item qunatity updated in cart.";
	        	}else{
	        		$responseArray['status'] = false;
		        	$responseArray['code'] = 500;
		        	$responseArray['message'] = "Somthing Went wrong, Please try after somtime";	
	        	}
        	}else{
				$responseArray['status'] = false;
	        	$responseArray['code'] = 500;
	        	$responseArray['message'] = "Invalid Request";
			}
			return response()->json($responseArray);
	}
	


    /*
     * Remove Item From Cart List
     */
    public function removeItemFromCartList(Request $request){
    		if(!empty($request->all())){
        		$userId	=  $request->get('user_id');
        		$itemId	=  $request->get('itemId');
        		if(\Cart::session($userId)->remove($itemId)){
        			$responseArray['status'] = true;
	        		$responseArray['code'] = 200;
	        		$responseArray['message'] = "Item Remove From Cart.";
	        	}else{
	        		$responseArray['status'] = false;
		        	$responseArray['code'] = 500;
		        	$responseArray['message'] = "Somthing Went wrong, Please try after somtime";	
	        	}
        	}else{
				$responseArray['status'] = false;
	        	$responseArray['code'] = 500;
	        	$responseArray['message'] = "Invalid Request";
			}
			return response()->json($responseArray);
    }






	

	/*
     * Get Cart List
     */
    public function getExpCartList(Request $request){
		
    	if(!empty($request->all())){
        	$userId 		=  $request->get('user_id');
			$cartItem = \Cart::session($userId)->getContent();
			if(!empty($cartItem)){
				// for a specific user
				$total = \Cart::session($userId)->getTotal();
				// for a specific user
				$subTotal = \Cart::session($userId)->getSubTotal();
				foreach($cartItem as $item){
        			$cart[]=array(
        				"name"=>$item['name'],
        				"price"=>number_format($item['price'],2),
        				"quantity"=>$item['quantity'],
        				"attributes"=>array(
								"itinerary_departures_id"	=>	$item['attributes']['itinerary_departures_id'],
								"itinerary_id"				=>	$item['attributes']['itinerary_id'],
								"itinerary_image"			=>	$item['attributes']['itinerary_image'],
								"itinerary_departure_date"	=>	$item['attributes']['itinerary_departure_date'],
	        			),

        			);
        		}

        		
				
			
		        $responseArray['status'] = true;
	        	$responseArray['code'] = 200;
	        	$responseArray['cart'] = $cart;
	        	$responseArray['total'] = number_format($total,2);
	        	$responseArray['subTotal'] = number_format($subTotal,2);
	        	$responseArray['TotalItem'] = count($cart);
	        	$responseArray['gst'] = $this->getGst();
	        	$gstValue = ($subTotal * $this->getGst())/100;
				$responseArray['gstAmount'] = number_format($gstValue,2);
				$responseArray['subTotalAfterGst'] = number_format(($subTotal + $gstValue),2);
				$subTotalAfterGst =  $subTotal + $gstValue;
				$responseArray['total'] = number_format($subTotalAfterGst,2);
				$responseArray['oid'] = encrypt(0);
				$responseArray['offerValue'] = "0.00";

				//Calulate Offer If applicable
				if(array_key_exists('offerId', $request->all())){

					$offerId = $request->get('offerId');
					//Check if this is from Checkout Page
					$pos = strpos($offerId, "chk");
					if($pos>0){
						$offerStr = str_replace('?chk=','',$offerId); 
						try{
						
							$offerId = decrypt($offerStr);

						}catch (DecryptException $e) {
				            $failedArray['status'] = false;
				            $failedArray['code'] = 500;
				            $failedArray['message'] = $e->getMessage();
				            return $failedArray;
				        }
					}
					if($offerId!=''){
						$offer = Offer::find($offerId);
						$cartItem = \Cart::session($userId)->getContent();
	        			if(!empty($cartItem)){
							$totalWithGst = $subTotalAfterGst;
							$newtotal = 0;
							$offerValue = 0;
							if($offer['offer_type']=='PERCENTAGE'){
								$offerValue = ($totalWithGst * $offer['offer_value'])/100;
								$newtotal = $totalWithGst - $offerValue;
							}
							if($offer['offer_type']=='FLAT'){
								$offerValue = $offer['offer_value'];
								$newtotal = $totalWithGst - $offer['offer_value'];
							}

						}
						$responseArray['total'] = number_format($newtotal,2);
						$responseArray['oid'] = encrypt($offerId);
						$responseArray['offerValue'] = number_format($offerValue,2);
					}
				}


			}else{
				$responseArray['status'] = false;
	        	$responseArray['code'] = 500;
	        	$responseArray['message'] = "Invalid Request";
			}
        }else{
			$responseArray['status'] = false;
	       	$responseArray['code'] = 500;
	       	$responseArray['message'] = "Invalid Request";
		}
        return response()->json($responseArray);
    }






    /*
     * Get Cart List
     */
    public function getCartList(Request $request){
    	if(!empty($request->all())){
			$userId 		=  $request->get('user_id');
			$cart =array();
			$cartItem = \Cart::session($userId)->getContent();
        	if(!empty($cartItem)){
        		// for a specific user
				$total = \Cart::session($userId)->getTotal();
				// for a specific user
				$subTotal = \Cart::session($userId)->getSubTotal();
				
        		foreach($cartItem as $item){
        			$cart[]=array(
        				"name"=>$item['name'],
        				"price"=>number_format($item['price'],2),
        				"quantity"=>$item['quantity'],
        				"attributes"=>array(
        					"seat_type_id"=>$item['attributes']['seat_type_id'],
        					"seating_type_name"=>$this->getSeatingTypeName($item['attributes']['seat_type_id']),
        					"event_timing_id"=>$item['attributes']['event_timing_id'],
        					"event_image"=>$item['attributes']['event_image'],
        					"event_booking_date"=>$item['attributes']['event_booking_date'],
	        			),

        			);
        		}

        		

		        $responseArray['status'] = true;
	        	$responseArray['code'] = 200;
	        	$responseArray['cart'] = $cart;
	        	$responseArray['total'] = number_format($total,2);
	        	$responseArray['subTotal'] = number_format($subTotal,2);
	        	$responseArray['TotalItem'] = count($cart);
	        	$responseArray['gst'] = $this->getGst();
	        	$gstValue = ($subTotal * $this->getGst())/100;
				$responseArray['gstAmount'] = number_format($gstValue,2);
				$responseArray['subTotalAfterGst'] = number_format(($subTotal + $gstValue),2);
				$subTotalAfterGst =  $subTotal + $gstValue;
				$responseArray['total'] = number_format($subTotalAfterGst,2);
				$responseArray['oid'] = encrypt(0);
				$responseArray['offerValue'] = "0.00";

				//Calulate Offer If applicable
				if(array_key_exists('offerId', $request->all())){

					$offerId = $request->get('offerId');
					//Check if this is from Checkout Page
					$pos = strpos($offerId, "chk");
					if($pos>0){
						$offerStr = str_replace('?chk=','',$offerId); 
						try{
						
							$offerId = decrypt($offerStr);

						}catch (DecryptException $e) {
				            $failedArray['status'] = false;
				            $failedArray['code'] = 500;
				            $failedArray['message'] = $e->getMessage();
				            return $failedArray;
				        }
					}
					if($offerId!=''){
						$offer = Offer::find($offerId);
						$cartItem = \Cart::session($userId)->getContent();
	        			if(!empty($cartItem)){
							$totalWithGst = $subTotalAfterGst;
							$newtotal = 0;
							$offerValue = 0;
							if($offer['offer_type']=='PERCENTAGE'){
								$offerValue = ($totalWithGst * $offer['offer_value'])/100;
								$newtotal = $totalWithGst - $offerValue;
							}
							if($offer['offer_type']=='FLAT'){
								$offerValue = $offer['offer_value'];
								$newtotal = $totalWithGst - $offer['offer_value'];
							}

						}
						$responseArray['total'] = number_format($newtotal,2);
						$responseArray['oid'] = encrypt($offerId);
						$responseArray['offerValue'] = number_format($offerValue,2);
					}
				}else{
					$responseArray['status'] = false;
	        		$responseArray['code'] = 500;
		        	$responseArray['message'] = "No Item into cart";
				}


			}else{
				$responseArray['status'] = false;
	        	$responseArray['code'] = 500;
	        	$responseArray['message'] = "Invalid Request";
			}
        }
        return response()->json($responseArray);
    }



    private function getGst(){
    	$tax = Tax::where('tax_type','=','GST')->first();
    	return $tax['value'];
    }

    private function getSeatingTypeName($seat_type_id){
    	$seatingType = SittingType::find($seat_type_id);
    	//print_r($seatingType );die;
    	if(!empty($seatingType)){
    		return $seatingType['sitting_type_name'];
    	}
    }


    public function addToCart(Request $request){
        if(!empty($request->all())){
        	$userId 		=  $request->get('user_id');
        	$seat_id 		=  $request->get('seat_id');
        	$bookingDate	=  $request->get('bookingDate');	
        	$event_timing_id=  $request->get('id');	
        	if($bookingDate==''){
	    		$responseArray['status'] = false;
	        	$responseArray['code'] = 500;
	        	$responseArray['message'] = "Date Of Booking Required.";
	        	return $responseArray;
	    	}
        	$seatArr = explode("|",$seat_id);	

        	//Check if this event timing into cart or not
        	$itmeId  = $event_timing_id.'-'.$seatArr[0];
        	$cartItem = \Cart::session($userId)->get($itmeId);
        	if(empty($cartItem)){
		        $responseArray = $this->addToCartItem($request);
			}else{
				$responseArray = $this->updateCartItem($request);
			}
        }
        return response()->json($responseArray);

	}



	/*
	* Add To Expericne into Cart 
	*/
	public function addToExpCart(Request $request){
        if(!empty($request->all())){
        	$userId 		=  $request->get('user_id');
        	$dept_id 		=  $request->get('dept_id');
        	$timing_id		=  $request->get('id');	
        	if($dept_id==''){
	    		$responseArray['status'] = false;
	        	$responseArray['code'] = 500;
	        	$responseArray['message'] = "Date Of Departure Required.";
	        	return $responseArray;
	    	}
        	//Check if this event timing into cart or not
        	$itmeId  = $timing_id.'-'.$dept_id;
        	$cartItem = \Cart::session($userId)->get($itmeId);
        	if(empty($cartItem)){
		        $responseArray = $this->addToExpCartItem($request);
			}else{
				$responseArray = $this->updateExpCartItem($request);
			}
        }
        return response()->json($responseArray);

	}
	

	 /*
     * Add Item to Cart
     * @param $request
     */
    private function addToExpCartItem($request){
    	$responseArray = array();
    	if(!empty($request->all())){
	    	$userId 		=  $request->get('user_id');
	    	$dept_id 		=  $request->get('dept_id');
			$itinerary_id=  $request->get('id');	
	    	//Get Details of Event Timing Detials Here
			$itinerary = Itinerary::with('ValidItineraryDeparture','ItineraryGallery')->find($itinerary_id)->toArray();
			$ItineraryDeparture = ItineraryDeparture::find($dept_id)->toArray();

	    	$itineraryImage =  $this->getExpImage($itinerary['itinerary_gallery']);
	    	$responseArray['message'] =$itinerary;
	        //return response()->json($responseArray);
			\Cart::session($userId);
			$attributes  = array(
				"itinerary_departures_id"=>$dept_id,
				"itinerary_id"=>$itinerary_id ,
				"itinerary_image"=>$itineraryImage,
				"itinerary_departure_date"=>$ItineraryDeparture['start_date'],
				"itinerary_price"=>$ItineraryDeparture['price']
			);
			// array format
			$itmeId  = $itinerary_id.'-'.$dept_id;
			$cartArray = array(
				'id'=>$itinerary_id,
				'name'=>$itinerary['title'],
				'price'=>$ItineraryDeparture['price'],
				'quantity' => 1,
				'attributes' => $attributes
			);
			//\Cart::session($userId)->clear();
			if(\Cart::add($cartArray)){
				$itmeId  = $itinerary_id.'-'.$dept_id;
				$cartItem = \Cart::session($userId)->get($itmeId);
				$responseArray['status'] = true;
		        $responseArray['code'] = 200;
	            $responseArray['message'] ="Item added into cart successfully.";
	            $responseArray['cartList'] =$cartItem;

			}else{
				$responseArray['status'] = false;
	        	$responseArray['code'] = 500;
	        	$responseArray['message'] = $e->getMessage();
			}
    	} 
    	return $responseArray;
	}


	
	private function getExpImage($event_gallery){
    	if(!empty($event_gallery)){
    		$count=1;
    		foreach ($event_gallery as $key => $value) {
    			if($count==1){
    				return env('APP_URL').'/storage/app/public/itinerary/'.$value['image'];
    			}
    		}
    	}
    }

	



	
	/*
     * Update Exp Item to Cart
     * @param $request
     */
    private function updateExpCartItem($request){
    	$responseArray = array();
    	if(!empty($request->all()))
    	{
	    	$userId 		=  $request->get('user_id');
	    	$seat_id 		=  $request->get('seat_id');
	    	$bookingDate	=  $request->get('bookingDate');	
	    	$event_timing_id=  $request->get('id');	

	    	//Get Details of Event Timing Detials Here
	    	$eventtiming = EventTiming::with('Theatre')->find($event_timing_id)->toArray();

	    	//Get Event Details
	    	$eventDetails = EventDetail::where('id','=',$eventtiming['event_detail_id'])->first()->toArray();
	    	$event = Event::with('EventDetail','EventGallery')->find($eventDetails['event_id'])->toArray();
			
	    	$theatrename = $eventtiming['theatre']['theater_name'];
	    	$eventImage =  $this->getEventImage($event['event_gallery']);
	    	$responseArray['message'] =$eventDetails;
	        //return response()->json($responseArray);

	    	$seatArr = explode("|",$seat_id);	
			\Cart::session($userId);
			$attributes  = array(
				"seat_type_id"=>$seatArr[0],
				"event_timing_id"=>$event_timing_id,
				"event_image"=>$eventImage,
				"event_booking_date"=>$bookingDate
			);
			// array format
			$itmeId  = $event_timing_id.'-'.$seatArr[0];
			$cartArray = array(
				'id'=>$itmeId,
				'name'=>$theatrename,
				'price'=>$seatArr[1],
				'quantity' => 1,
				'attributes' => $attributes
			);
			if(\Cart::session($userId)->update($itmeId, $cartArray)){
				$itmeId  = $event_timing_id.'-'.$seatArr[0];
				$cartItem = \Cart::session($userId)->get($itmeId);
				$responseArray['status'] = true;
		        $responseArray['code'] = 200;
	            $responseArray['message'] ="cart updated successfully.";
	            $responseArray['cartList'] =$cartItem;
			}else{
				$responseArray['status'] = false;
	        	$responseArray['code'] = 500;
	        	$responseArray['message'] = $e->getMessage();
			}
		}
		return $responseArray;

	}
	

    /*
     * Update Item to Cart
     * @param $request
     */
    private function updateCartItem($request){
    	$responseArray = array();
    	if(!empty($request->all()))
    	{
	    	$userId 		=  $request->get('user_id');
	    	$seat_id 		=  $request->get('seat_id');
	    	$bookingDate	=  $request->get('bookingDate');	
	    	$event_timing_id=  $request->get('id');	

	    	//Get Details of Event Timing Detials Here
	    	$eventtiming = EventTiming::with('Theatre')->find($event_timing_id)->toArray();

	    	//Get Event Details
	    	$eventDetails = EventDetail::where('id','=',$eventtiming['event_detail_id'])->first()->toArray();
	    	$event = Event::with('EventDetail','EventGallery')->find($eventDetails['event_id'])->toArray();
			
	    	$theatrename = $eventtiming['theatre']['theater_name'];
	    	$eventImage =  $this->getEventImage($event['event_gallery']);
	    	$responseArray['message'] =$eventDetails;
	        //return response()->json($responseArray);

	    	$seatArr = explode("|",$seat_id);	
			\Cart::session($userId);
			$attributes  = array(
				"seat_type_id"=>$seatArr[0],
				"event_timing_id"=>$event_timing_id,
				"event_image"=>$eventImage,
				"event_booking_date"=>$bookingDate
			);
			// array format
			$itmeId  = $event_timing_id.'-'.$seatArr[0];
			$cartArray = array(
				'id'=>$itmeId,
				'name'=>$theatrename,
				'price'=>$seatArr[1],
				'quantity' => 1,
				'attributes' => $attributes
			);
			if(\Cart::session($userId)->update($itmeId, $cartArray)){
				$itmeId  = $event_timing_id.'-'.$seatArr[0];
				$cartItem = \Cart::session($userId)->get($itmeId);
				$responseArray['status'] = true;
		        $responseArray['code'] = 200;
	            $responseArray['message'] ="cart updated successfully.";
	            $responseArray['cartList'] =$cartItem;
			}else{
				$responseArray['status'] = false;
	        	$responseArray['code'] = 500;
	        	$responseArray['message'] = $e->getMessage();
			}
		}
		return $responseArray;

    }



     /*
     * Add Item to Cart
     * @param $request
     */
    private function addToCartItem($request){
    	$responseArray = array();
    	if(!empty($request->all())){
	    	$userId 		=  $request->get('user_id');
	    	$seat_id 		=  $request->get('seat_id');
	    	$bookingDate	=  $request->get('bookingDate');	
	    	$event_timing_id=  $request->get('id');	
	    	//Get Details of Event Timing Detials Here
	    	$eventtiming = EventTiming::with('Theatre')->find($event_timing_id)->toArray();

	    	//Get Event Details
	    	$eventDetails = EventDetail::where('id','=',$eventtiming['event_detail_id'])->first()->toArray();
	    	$event = Event::with('EventDetail','EventGallery')->find($eventDetails['event_id'])->toArray();
			$eventName = $event['event_detail'][0]['event']['title'];
			$theatrename = $eventtiming['theatre']['theater_name'];
	    	$eventImage =  $this->getEventImage($event['event_gallery']);
	    	$responseArray['message'] =$eventDetails;
	        //return response()->json($responseArray);

	    	$seatArr = explode("|",$seat_id);	
			\Cart::session($userId);
			$attributes  = array(
				"seat_type_id"=>$seatArr[0],
				"event_timing_id"=>$event_timing_id,
				"event_image"=>$eventImage,
				"event_booking_date"=>$bookingDate
			);
			// array format
			$itmeId  = $event_timing_id.'-'.$seatArr[0];
			$cartArray = array(
				'id'=>$itmeId,
				'name'=>$eventName,
				'price'=>$seatArr[1],
				'quantity' => 1,
				'attributes' => $attributes
			);

			if(\Cart::add($cartArray)){
				$itmeId  = $event_timing_id.'-'.$seatArr[0];
				$cartItem = \Cart::session($userId)->get($itmeId);
				$responseArray['status'] = true;
		        $responseArray['code'] = 200;
	            $responseArray['message'] ="Item added into cart successfully.";
	            $responseArray['cartList'] =$cartItem;

			}else{
				$responseArray['status'] = false;
	        	$responseArray['code'] = 500;
	        	$responseArray['message'] = $e->getMessage();
			}
    	} 
    	return $responseArray;
	}




	/*
	 *
	 */
	public function checkOfferCode(Request $request){
		$responseArray = array();
    	if(!empty($request->all())){
	    	$userId 		=  $request->get('user_id');
	    	$code 			=  trim($request->get('code'));
	    	
	    	$offer = Offer::where('offer_code','=',$code)->first();
	    	if(!empty($offer)){

	    		//check if this code is valid today or not
	    		$fromdate 	= strtotime($offer['valid_from']);
	    		$toDate 	= strtotime($offer['valid_untill']);
	    		$now = time();
	    		if(($now >= $fromdate) && ($now <= $toDate)){
	    			$responseArray['status'] = true;
			        $responseArray['code'] = 200;
		            $responseArray['message'] ="Code applied successfully.";

		            $cartItem = \Cart::session($userId)->getContent();
        			if(!empty($cartItem)){
	        			// for a specific user
						$total = \Cart::session($userId)->getTotal();
						// for a specific user
						$subTotal = \Cart::session($userId)->getSubTotal();
						$newtotal = 0;
						$offerValue = 0;
						if($offer['offer_type']=='PERCENTAGE'){
							$offerValue = ($subTotal * $offer['offer_value'])/100;
							$newtotal = $subTotal - $offerValue;
						}
						if($offer['offer_type']=='FLAT'){
							$offerValue = $offer['offer_value'];
							$newtotal = $subTotal - $offer['offer_value'];
						}

					}

	    	        $responseArray['data'] =array('id'=>$offer['id'],
	    	        	'type'=>$offer['offer_type'],
	    	        	'value'=>$offer['offer_value'],
	    	        	'subtotal'=>$newtotal,
	    	        	'offerValue'=>number_format($offerValue,2));	
	    		}else{
	    			$responseArray['status'] = false;
			        $responseArray['code'] = 500;
		            $responseArray['message'] ="Coupon Code is expired.";
	    		}

	    		
        	}else{
        		$responseArray['status'] = false;
		        $responseArray['code'] = 500;
	            $responseArray['message'] ="Invalid Code.";
        	}

		}else{
			$responseArray['status'] = false;
        	$responseArray['code'] = 500;
        	$responseArray['message'] = $e->getMessage();
		}
		return $responseArray;

	}
























}
