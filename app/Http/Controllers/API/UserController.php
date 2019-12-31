<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MasterController;
use Auth;
use App\User;
use Session;
use App\Order;
use Illuminate\Support\Facades\Hash;
use App\Setting;
use App\Event;
use App\EventDetail;
use App\EventGallery;
use App\SittingType;
use App\Theatre;
use App\Price;
use Carbon\Carbon;
class UserController extends MasterController
{
     
     public $successStatus = 200;
     /**
     *@Author       : Pradeep Kumar
     *@Description  : Register API 
     *@Created Date : 24 APR 2019
     */
    public function register(Request $request){
            

            Validator::extend('valid_username', function($attr, $value){
                return preg_match('/^\S*$/u', $value);
             });
           $validator = Validator::make($request->all(), [
                'firstName' => 'required|max:50|min:3',
                'lastName' => 'required|max:50|min:3',
                'password' => 'required|min:6',
                'username' => 'required|min:4|valid_username|unique:users,username',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:6',
                'password_confirmation' => 'required|min:6'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                $responseArray['status'] = false;
                $responseArray['message']= "Input are not valid";
                $responseArray['error']= $errors;
            }else{
                $userObj = new User();
                $userObj->first_name = $request->get('firstName');
                $userObj->last_name = $request->get('lastName');
                $userObj->password = Hash::make($request->get('password'));
                $userObj->username = $request->get('username');
                $userObj->email = $request->get('email');
                $userObj->created_at = self::getCreatedDate();
                try{
                    $userArr =  array();
                    $userObj->save();
                    $last_insert_id = $userObj->id;
                    $userData   = User::find($userObj->id);
                    //$this->sendEmail($last_insert_id,$request);
                    $userArr['id']      =   encrypt($userObj->id);
                    $userArr['name']    =   $userObj->first_name.' '.$userObj->last_name;
                    $userArr['email']   =   $userObj->email;
                    $responseArray['code']          =   '200';
                    $responseArray['status']        =   'success';
                    $responseArray['message']       =   "User Register Successfully.";
                    $responseArray['data']['user']  =   $userArr;
                    

                }catch (Exception $e) {
                    $responseArray['code'] = 403;
                    $responseArray['status'] = 'error';
                    $responseArray['message'] = $e->getMessage();
                }
            }        
        return response()->json($responseArray);
    }









    private function checkEmail($email) {
        $find1 = strpos($email, '@');
        $find2 = strpos($email, '.');
        return ($find1 !== false && $find2 !== false && $find2 > $find1);
    }



    private function getSetting(){
        $setting = Setting::all();
        return $setting;
    }



    /**
     *@Author       : Pradeep Kumar
     *@Description  : Login Authentication API 
     *@Created Date : 24 Apr 2019
     */
    public function login(Request $request) {
        if(self::isValidToekn($request)){
            try{
                    $validator = Validator::make($request->all(), [
                        'username' => 'required',
                        'password' => 'required',
                    ]);
                    if ($validator->fails()) {
                        $errors = $validator->errors();
                        $responseArray['status'] = false;
                        $responseArray['message']= "Input are not valid";
                        $responseArray['error']= $errors;
                    }else{

                            $email = $request->get('username');
                            if ($this->checkEmail($email) ) {
                                $credentials = array('email'=>$email, 'password'=>$request->get('password'));
                            }else{
                                $user = User::where('username','=',$email)->first();
                                $credentials = array('email'=>$user['email'], 'password'=>$request->get('password'));
                            }
                            if (Auth::attempt($credentials)) {
                            	$user = User::find(Auth::user()->id);
                                $userId = Auth::user()->id;
                                $email =  Auth::user()->email;
                                $username =  Auth::user()->username;
                                $first_name =  Auth::user()->first_name;
                                $last_name =  Auth::user()->last_name;
                                $created_at =  date("d M, Y",strtotime(Auth::user()->created_at->toDateTimeString()));
                                $userDetails = array(
                                    'id'        =>  $userId,
                                    'email'     =>  $email,
                                    'username'  =>  $username,
                                    'first_name'=>  $first_name,
                                    'last_name' =>  $last_name, 
                                    'created_at' =>  $created_at,                                
                                );
                                $responseArray['status'] = 'success';
                                $responseArray['code'] = '200';
                                $responseArray['user'] = $userDetails;
                                $responseArray['token'] = $user->createToken('MyApp')->accessToken;
                                //return redirect()->intended('dashboard');
                            }else{
                                $responseArray['status'] = false;
                                $responseArray['message'] = "Invalid Credentials Request.";
                            }
                    }

            }catch (Exception $e) {
                $responseArray['status'] = 'error';
                $responseArray['message'] = $e->getMessage();
            }
        }else{
            $responseArray = self::getInvalidTokenMsg();
        }
        return response()->json($responseArray);
    }



    /* details api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function details() 
    { 
        $user = Auth::user(); 
        return response()->json(['success' => $user], $this->successStatus); 
    } 



    public function getUserList(Request $request){
        $responseArray = array();
        $userList = User::with('Order')->paginate(10000);
        $links = $userList->links();
       

        /****************Datatable Start***************/
        $columns = array(
            array('label'=>'SN','field'=>'SN','sort'=>'asc','width'=>'25'),
            array('label'=>'Name','field'=>'first_name','sort'=>'asc','width'=>'170'),
            array('label'=>'Username','field'=>'username','sort'=>'asc','width'=>'100'),
            array('label'=>'Email','field'=>'email','sort'=>'asc','width'=>'100'),
            array('label'=>'Phone','field'=>'phone','sort'=>'asc','width'=>'100'),
            array('label'=>'Address','field'=>'street_address','sort'=>'asc','width'=>'100'),
            array('label'=>'City','field'=>'city','sort'=>'asc','width'=>'100'),
            array('label'=>'Pincode','field'=>'postcode','sort'=>'asc','width'=>'100'),
            array('label'=>'Status','field'=>'status','sort'=>'asc','width'=>'100'),
            array('label'=>'Created On','field'=>'created_at','sort'=>'asc','width'=>'100'),
            array('label'=>'Action','field'=>'action','sort'=>'asc','width'=>'100')
            );
        $row = [];
        $actionStr ='';
        $i=1;
        foreach($userList as $item){
            $date = new Carbon($item['created_at']->toDateTimeString());
            $row[] =array(
                'SN'=>$i,
                'id'=>$item['id'],
                'first_name'=>($item['first_name']!='')?$item['first_name']:"--",
                'username'=>($item['username']!='')?$item['username']:"--",
                'email'=>($item['email']!='')?$item['email']:"--",
                'phone'=>($item['phone']!='')?$item['phone']:"--",
                'street_address'=>($item['street_address']!='')?$item['street_address']:"--",
                'city'=>($item['city']!='')?$item['city']:"--",
                'postcode'=>($item['postcode']!='')?$item['postcode']:"--",
                'status'=>$item['status'],
                'created_at'=>date("d-M-Y",strtotime($item['created_at']->toDateTimeString())),
                'action'=>$actionStr
            );
            $i++;
        }
        $dataTable = array();
        $dataTable['columns'] =$columns;
        $dataTable['rows'] =$row;
        /****************Datatable Ends now***************/

        $responseArray['status'] = 'success';
        $responseArray['code'] = '200';
        $responseArray['user'] = $userList;
        $responseArray['dataTable'] = $dataTable;
        return response()->json(['data' => $responseArray], $this->successStatus); 
    }



    public function userEventOrderList(Request $request){
        $setting = $this->getSetting();
        $responseArray = array();
        $orderList = "";
        $id = $request->get('id');
        $order_id = $request->get('order_id');
        $order_type = $request->get('order_type');
        if($order_id!=''){
            $orderList = Order::with('OrderStatus','User')->where('id','=',$order_id)->where('user_id','=',$user_id)->orderBy('id','DESC')->paginate(1000);
        }else{
            if($order_type!=''){
                $orderList = Order::with('OrderStatus','User')->where('order_type','=',$order_type)->where('user_id','=',$id)->orderBy('id','DESC')->paginate(1000);
            }else{
                $orderList = Order::with('OrderStatus','User')->where('user_id','=',$id)->orderBy('id','DESC')->paginate(10000);
            }
        }

        //Get User Details
        $user = User::with('MembershipPlanOrder')->find($id)->toArray();

        //Get
        if($orderList->count()){
            $responseArray['status'] = 'success';
            $responseArray['code'] = '200';
            $responseArray['orderList'] = $orderList;
            $responseArray['userDetails'] = $user;
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['orderList'] = $orderList;
        }
        return response()->json(['data' => $responseArray,'user'=>$user,'settings'=>$setting], $this->successStatus); 
        
    }



    public function userupdate(Request $request){
        $responseArray = array();
        $responseArray['status'] = 'error';
        $responseArray['code'] = '500';
        if ($request->isMethod('post')) {
           Validator::extend('valid_username', function($attr, $value){
               return preg_match('/^\S*$/u', $value);
           });
           $data = $request->get('user');
           $validator = Validator::make($request->get('user'), [
                'id' => 'required',
                'first_name' => 'required|max:50|min:3',
                'last_name' => 'required|max:50|min:3',
                'username' => 'required|min:4',
                'email' => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                $responseArray['status'] = false;
                $responseArray['message']= "Input are not valid";
                $responseArray['error']= $errors;
            }else{
                $user = User::find($data['id']);
                if(!empty($user)){
                   $user->first_name = $data['first_name'];
                   $user->last_name  = $data['last_name'];
                   $user->email      = $data['email'];
                   $user->username   = $data['username'];
                   $user->status     = $data['status'];
                   if($user->save()){
                       $responseArray['status'] = 'success';
                       $responseArray['code'] = '200';
                       $responseArray['message'] = "User update successfully.";
                   }else{
                        $responseArray['status'] = 'error';
                        $responseArray['code'] = '500';
                        $responseArray['message'] = "Opps!! Somthing went wrong, pelase try after sometime";
                   }
                }else{
                    $responseArray['status'] = 'error';
                    $responseArray['code'] = '500';
                    $responseArray['message'] = "User not found!!.";
                }
     
                
            }
            
        }
        return response()->json(['data' => $responseArray], $this->successStatus); 

    }


     public function getEventDetailsByEventDetailsID($event_detail_id){
        $responseArray = array();
        $eventDetails = EventDetail::find($event_detail_id);
        $id = $eventDetails['event_id'];
        $event = Event::with('EventDetail','EventGallery')->find($id)->toArray();
        unset($event['event_detail'][0]['event_timing'][0]['theatre']['event_seat']);
        foreach($event['event_detail'][0]['event_timing'][0]['price'] as $k=>$v){
            $priceArr[]=array(
                    'event_timing_id'=>$v['event_timing_id'],
                    'sitting_type_id'=>$v['sitting_type_id'],
                    'price'=>$v['price'],
                    'sitting_type'=>$v['sitting_type']['sitting_type_name']
            );
        }
        $event['event_detail'][0]['event_timing'][0]['price'] = $priceArr;
       

        $imgGalleryList = array();
        foreach($event['event_gallery'] as $item){
            $imgGalleryList[] = array(
                "src"=>env('APP_URL').'/storage/app/public/event/'.$item['image'],
                "thumbnail"=>env('APP_URL').'/storage/app/public/event/'.$item['image'],
                "thumbnailWidth"=>rand(250,375),
                "thumbnailHeight"=>rand(175,250),
                "caption"=>"",
                "id"=>$item['id'],
                "is_default"=>$item['is_default'],
                "status"=>$item['status'],
            );
        }
        unset($event['event_gallery']);
        if(!empty($event)){
            $event['imagesList'] = $imgGalleryList[0];
        }else{
            $event= array();
        }
        return $event; 

    }

    //Ge SittingType
    private function getSeatingTypeById($id){
        $seatType=SittingType::select('sitting_type_name')->find($id)->toArray();
        return $seatType;
    }

    //Ge SittingType
    private function getTheatreById($theatre_id){
        $name=Theatre::find($theatre_id)->toArray();
        return $name;
    }


    //Get Price of Event timing
    private function getPriceByEventTiming($event_timing_id,$sitting_type_id){
        $price = array();
        $price = Price::where('event_timing_id','=',$event_timing_id)->where('sitting_type_id','=',$sitting_type_id)->get()->toArray();
        if(!empty($price)){
            return $price[0]; 
        }else{
            return $price; 
        }
    }

    

    
    private function getDefaultImage($gallery){
        $imgStr = '';
        if(!empty($gallery)){
            $imgStr = '';
            foreach($gallery as $item){
                if($item['is_default']==1){
                    $imgStr = $item['image'];
                }
            }
            if($imgStr==''){
                $imgStr = $gallery[0]['image'];
            }
        }
        return env('APP_URL').'/storage/app/public/itinerary/'.$imgStr;
    }


    public function userTravelOrderDetails(Request $request){
        $setting = $this->getSetting();
        $orderId = $request->get('order_id');
        $orderDetails = Order::with('OrderStatus','TempSeatBooking','ItineraryBooking')->where('orderId','=',$orderId)->first()->toArray();
        //print_r($orderDetails);die;
        $eventTiming = array();
        //Formate all the Quantity for the Order  
        $seatCount  = array();
        $keyArray = array();
        $finalArray = array();
        foreach($orderDetails['itinerary_booking'] as $k=>$item){
            if(!empty($item['itinerary'])){
                $orderDetails['itinerary_booking'][$k]['itinerary']['image']=$this->getDefaultImage($item['itinerary']['itinerary_gallery']);
            }
            unset($orderDetails['itinerary_booking'][$k]['itinerary']['itinerary_gallery']);
        }
    
        //Get User Details
       
        $user = User::find($orderDetails['user_id']);
        if(!empty($orderDetails)){
            $responseArray['status'] = 'success';
            $responseArray['code'] = '200';
            $responseArray['orderDetails'] = array($orderDetails);
            $responseArray['User'] = $user;
            $responseArray['settings'] = $setting;


            
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['message'] = "User not found!!.";
        }
        return response()->json(['data' => $responseArray], $this->successStatus); 
    }

    public function userEventOrderDetails(Request $request){
        $setting = $this->getSetting();
        $orderId = $request->get('order_id');
        $orderParent = Order::where('orderId','=',$orderId)->get()->toArray();
        $orderType = 1;
        $responseArray = array();
        if(!empty($orderParent)){
           $orderType = $orderParent[0]['order_type'];
        }

        if($orderType==1){
            $responseArray = $this->getEventOrderInvoice($orderId);
        }
        if($orderType==2){
            $responseArray = $this->getTravelOrderInvoice($orderId);
        }

        return response()->json(['data' => $responseArray], $this->successStatus); 
        }



        private function getTravelOrderInvoice($orderId){
            $setting = $this->getSetting();
            $orderId = $orderId;
            $orderDetails = Order::with('OrderStatus','TempSeatBooking','ItineraryBooking')->where('orderId','=',$orderId)->first()->toArray();
            //print_r($orderDetails);die;
            $eventTiming = array();
            //Formate all the Quantity for the Order  
            $seatCount  = array();
            $keyArray = array();
            $finalArray = array();
            foreach($orderDetails['itinerary_booking'] as $k=>$item){
                if(!empty($item['itinerary'])){
                    $orderDetails['itinerary_booking'][$k]['itinerary']['image']=$this->getDefaultImage($item['itinerary']['itinerary_gallery']);
                }
                unset($orderDetails['itinerary_booking'][$k]['itinerary']['itinerary_gallery']);
            }
        
            //Get User Details
        
            $user = User::find($orderDetails['user_id']);
            if(!empty($orderDetails)){
                $responseArray['status'] = 'success';
                $responseArray['code'] = '200';
                $responseArray['orderDetails'] = array($orderDetails);
                $responseArray['User'] = $user;
                $responseArray['settings'] = $setting;


                
            }else{
                $responseArray['status'] = 'error';
                $responseArray['code'] = '500';
                $responseArray['message'] = "User not found!!.";
            }
            return $responseArray;
        }


        private function getEventOrderInvoice($orderId){
            $setting = $this->getSetting();
            $orderDetails = Order::with('OrderStatus','TempSeatBooking')->where('orderId','=',$orderId)->first()->toArray();
            //print_r($orderDetails);die;
            $eventTiming = array();
            //Formate all the Quantity for the Order  
            $seatCount  = array();
            foreach($orderDetails['temp_seat_booking'] as $k=>$item){
                if(!empty($item['event_seat'])){
                    unset($orderDetails['temp_seat_booking'][$k]['event_timing']['itinerary']);
                    unset($orderDetails['temp_seat_booking'][$k]['event_timing']['includes']);
                    unset($orderDetails['temp_seat_booking'][$k]['event_timing']['other']);
                    unset($orderDetails['temp_seat_booking'][$k]['event_timing']['dincludes']);
                    unset($orderDetails['temp_seat_booking'][$k]['event_timing']['status']);
                    unset($orderDetails['temp_seat_booking'][$k]['event_timing']['created_at']);
                    unset($orderDetails['temp_seat_booking'][$k]['event_seat']['created_at']);
                    unset($orderDetails['temp_seat_booking'][$k]['event_timing']['updated_at']);
                    unset($orderDetails['temp_seat_booking'][$k]['event_seat']['updated_at']);
                    unset($orderDetails['temp_seat_booking'][$k]['created_at']);
                    unset($orderDetails['temp_seat_booking'][$k]['updated_at']);
                    $seatTypeName = $this->getSeatingTypeById($item['event_seat']['sitting_type_id']);
                    $orderDetails['temp_seat_booking'][$k]['event_seat']['SeatType']=$seatTypeName['sitting_type_name'];

                    
                    $theatreName = $this->getTheatreById($item['event_seat']['theatre_id']);
                    $orderDetails['temp_seat_booking'][$k]['event_seat']['Theatre']=$theatreName['theater_name'];

                    //Get All Price List of this Event Timing
                    $sitting_type_id = $orderDetails['temp_seat_booking'][$k]['event_seat']['sitting_type_id'];
                    $event_timing_id = $orderDetails['temp_seat_booking'][$k]['event_timing']['id'];
                    $price = $this->getPriceByEventTiming($event_timing_id,$sitting_type_id);
                    $orderDetails['temp_seat_booking'][$k]['event_seat']['Price']=$price['price'];

                    $event = $this->getEventDetailsByEventDetailsID($item['event_timing']['event_detail_id']);
                    if($event['event_detail'][0]['event_timing'][0]['id']==$event_timing_id){
                        $orderDetails['temp_seat_booking'][$k]['Event']=$event['event_detail'][0]['event'];
                        $orderDetails['temp_seat_booking'][$k]['EventImage']=$event['imagesList'];
                    }
                    $eventTiming[$item['event_timing_id']][]=array('event'=>$event,'seat'=>$item['event_seat']);
                    $seatCount[$item['event_timing_id']][]=array($orderDetails['temp_seat_booking'][$k]['event_seat']['position_row'].$orderDetails['temp_seat_booking'][$k]['event_seat']['position_column']);
                
                }
            }
            
        $eventDetailArrayWithSeat = array();
        foreach($eventTiming as $k=>$finalItem){
                $eventDetailArray[]=$finalItem;
        }
            $keyArray = array();
            $finalArray = array();
            if(!empty($orderDetails['temp_seat_booking'])){
                foreach($orderDetails['temp_seat_booking'] as $k=>$item){
                    if(!array_key_exists($item['event_timing_id'],$keyArray)){    
                        if(array_key_exists($item['event_timing_id'],$seatCount)){
                            $orderDetails['temp_seat_booking'][$k]['Seat'] =  $seatCount[$item['event_timing_id']];   
                        }
                        $finalArray[] = $orderDetails['temp_seat_booking'][$k];
                        $keyArray[$item['event_timing_id']]=$item['event_timing_id'];
                    }
                }
            }

            //Get User Details
            $user = User::find($orderDetails['user_id']);
            if(!empty($orderDetails)){
                $responseArray['status'] = 'success';
                $responseArray['code'] = '200';
                $responseArray['orderDetails'] = $orderDetails;
                $responseArray['orderDetails']['temp_seat_booking'] = $finalArray;
                $responseArray['User'] = $user;
                $responseArray['settings'] = $setting;


                
            }else{
                $responseArray['status'] = 'error';
                $responseArray['code'] = '500';
                $responseArray['message'] = "Order not found!!.";
            }
            return $responseArray;
        }



        public function userdetails(Request $request){
            $setting = $this->getSetting();
            if($request->isMethod('post'))
            {
                $data = $request->all();
                $id= $data['id'];
                $user = User::with('Order','State','City','Country')->find($id)->toArray();
                if(!empty($user)){
                    $responseArray['status'] = 'success';
                    $responseArray['code'] = '200';
                    $responseArray['userData'] =$user;
                    $responseArray['settings'] = $setting;
                    
                }else{
                    $responseArray['status'] = 'error';
                    $responseArray['code'] = '500';
                    $responseArray['message'] = 'Somthing went wrong!! Please try after sometime';
                }
            }else{
                $responseArray['status'] = 'error';
                $responseArray['code'] = '500';
                $responseArray['message'] = 'Invalid Requets';
    
            }
            return response()->json($responseArray, $this->successStatus); 
        }









    public function userUpdateProfile(Request $request){
        $responseArray = array();

        $responseArray['status'] = 'error';
        $responseArray['code'] = '500';
        if ($request->isMethod('post')) {
           $data = $request->get('user');
            if(array_key_exists('oldpwd',$data)){
                $passowrd = $data['oldpwd'];
                $userData = User::find($data['id']);
                $email = $userData['email'];
                $credentials = array('email'=>$email, 'password'=>$passowrd);
                if (Auth::attempt($credentials)) {
                    $user = User::find($data['id']);
                    $user->password  = Hash::make($data['cpwd']);
                    if($user->save()){
                        $responseArray['status'] = 'success';
                        $responseArray['code'] = '200';
                        $responseArray['message'] = "!! Password Changed Successfully !!";
                    }else{
                            $responseArray['status'] = 'error';
                            $responseArray['code'] = '500';
                            $responseArray['message'] = "Opps!! Somthing went wrong, pelase try after sometime";
                    }
                }else{
                    $responseArray['status'] = 'error';
                    $responseArray['code'] = '500';
                    $responseArray['message'] = "Invlaid Old Password";
                }
            }else{
                    $validator = Validator::make($request->get('user'), [
                            'id' => 'required',
                            'first_name' => 'required|max:50|min:3',
                            'last_name' => 'required|max:50|min:3',
                            'street_address' => 'required|max:150|min:3',
                            'address_2' => 'required|max:150|min:3',
                            'country_id' => 'required|max:150|min:1',
                            'state_id' => 'required|max:150|min:1',
                            'city_id' => 'required|max:150|min:1',
                            'phone' => 'required|max:20|min:10',
                        ]);
                        if ($validator->fails()) {
                            $errors = $validator->errors();
                            $responseArray['status'] = false;
                            $errorStr ='';
                            if(!empty($errors)){
                                foreach($errors->all() as $value){
                                    $errorStr.=$value;
                                }
                            }
                            $responseArray['message']= "Input are not valid, ".$errorStr;
                            $responseArray['error']= $errors;
                        }else{
                            $user = User::find($data['id']);
                            
                            if(!empty($user)){
                            $user->first_name      = $data['first_name'];
                            $user->last_name       = $data['last_name'];
                            $user->street_address  = $data['street_address'];
                            $user->address_2       = $data['address_2'];
                            $user->phone           = $data['phone'];
                            $user->postcode        = $data['postcode'];
                            if(is_numeric($data['country_id'])){
                                    $user->country_id        = $data['country_id'];
                            }
                            if(is_numeric($data['state_id'])){
                                $user->state_id        = $data['state_id'];
                            }
                            if(is_numeric($data['city_id'])){
                                $user->city_id        = $data['city_id'];
                            }
                            if($user->save()){
                                $responseArray['status'] = 'success';
                                $responseArray['code'] = '200';
                                $responseArray['message'] = "User update successfully.";
                            }else{
                                    $responseArray['status'] = 'error';
                                    $responseArray['code'] = '500';
                                    $responseArray['message'] = "Opps!! Somthing went wrong, pelase try after sometime";
                            }
                            }else{
                                $responseArray['status'] = 'error';
                                $responseArray['code'] = '500';
                                $responseArray['message'] = "User not found!!.";
                            }
                
                            
                            }
            }
            
        }
        return response()->json(['data' => $responseArray], $this->successStatus); 

    }






















}
