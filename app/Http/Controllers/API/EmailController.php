<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\PDF;
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
use Mail;
use Illuminate\Contracts\Encryption\DecryptException;
class EmailController extends MasterController
{
     
     /**
     *@Author       : Pradeep Kumar
     *@Description  : Rest Link Sent to User for change password
     *@Created Date : 21 JAN 2020
     */
     
     public static function ResetEmail($user) {
        $setting = self::getSetting(); 
        $data = array(
            'site_url'=>config('app.site_url'),
            'reset_password_link'=>config('app.site_url').config('app.reset_password_link').encrypt($user['email']),
            'user_email'=>$user['email'],
            'user_name'=>$user['first_name'],
            'admin_email'=>$setting[25]['options_value']
        );
        Mail::send('Email.resetPassword',  ['data' => $data], function($message) use ($data) {
           $message->to($data['user_email'])->subject('Reset Your Rudra Password');
           $message->from($data['admin_email'],config('app.mail_from_name'));
        });
        //echo "Email Sent with attachment. Check your inbox.";
     }






      /**
     *@Author       : Pradeep Kumar
     *@Description  : Welcome Email to User after registering him self
     *@Created Date : 22 JAN 2020
     */
     
    public static function WelcomeEmail($user){
        $setting = self::getSetting(); 
        $data = array(
            'site_url'=>config('app.site_url'),
            'user_email'=>$user['email'],
            'user_name'=>$user['first_name'].' '.$user['last_name'],
            'admin_email'=>$setting[25]['options_value']
        );
        Mail::send('Email.welcomeUser',  ['data' => $data], function($message) use ($data) {
           $message->to($data['user_email'])->subject('Thank your for register with RudraXP');
           $message->cc($data['admin_email'])->subject('New User Register with RudraXP');
           $message->from($data['admin_email'],config('app.mail_from_name'));
        });
        //echo "Email Sent with attachment. Check your inbox.";
     }


     //View Email on Web
     public static function ViewEventBookingEmail($encryptOrderId){
        $orderID = decrypt($encryptOrderId); 
        $setting = self::getSetting(); 
        $invoiceArr = app(UserController::class)->getEventOrderInvoice($orderID);
        $getGst = app(CartController::class)->getGst();
        $orderDetails =  $invoiceArr['orderDetails'];

        $orderDetailsArray  = array(
            'orderNo'=>$orderDetails['orderID'],
            'orderDate'=>date('d M Y',strtotime($orderDetails['order_date'])),
            'orderAmount'=>$setting[14]['options_value'].$orderDetails['total_amount'],
            'user'=>array('first_name'=>$orderDetails['shipping_fname'],'last_name'=>$orderDetails['shipping_lname'],'email'=>$orderDetails['shipping_email']),
            'temp_seat_booking'=>$orderDetails['temp_seat_booking'],
            'order_status'=>$orderDetails['order_status']
        );

        
        //Event Booked Array
        if(!empty($orderDetailsArray['temp_seat_booking'])){
            foreach($orderDetailsArray['temp_seat_booking'] as $item){
               
                $Seat = [];
                if(!empty($item['Seat'])){
                    foreach($item['Seat'] as $k=>$val){
                        $Seat[] = $val[0];
                    }
                }

                $eventArr[]= array(
                                    'start_time'=> $item['event_timing']['event_start_time'],
                                    'end_time'  => $item['event_timing']['event_end_time'],
                                    'event_seat'=> $item['event_seat']['SeatType'],
                                    'price'     => $item['event_seat']['Price'],
                                    'Theatre'   => $item['event_seat']['Theatre'],
                                    'Event'     => $item['Event']['title'],
                                    'durration' => $item['Event']['durration'],
                                    'EventImageSrc'=> $item['EventImage']['src'],
                                    'EventImageThumb'=> $item['EventImage']['thumbnail'],
                                    'Seat'=>implode(',',$Seat),
                                    'quantity'=>count($Seat),
                                    'priceType'=>$setting[14]['options_value'],
                                    'eventURL'=>env('SITE_URL').'/day-exp-detail/'.$item['Event']['id'].'-'.$item['event_timing_id']
                                  );
            }
        }

        $moreInfo = array(
            'company_name'=> $setting[17]['options_value'],
            'company_address'=> $setting[16]['options_value'],
            'phone'=> $setting[2]['options_value'],
            'mobile'=> $setting[3]['options_value'],
            'email'=> $setting[1]['options_value'],

            'name'=> $orderDetails['shipping_fname'].' '.$orderDetails['shipping_lname'],
            'address'=>$orderDetails['shipping_address1'].' '.$orderDetails['shipping_address2'],
            'location'=>$orderDetails['shipping_city'].','.$orderDetails['shipping_state'].','.$orderDetails['shipping_pincode'],
            'uphone'=>$orderDetails['shipping_mobile'],
            'uemail'=>$orderDetails['email_address'],
        );



        $data = array(
            'site_url'          =>  config('app.site_url'),
            'user_email'        =>  $orderDetailsArray['user']['email'],
            'user_name'         =>  $orderDetailsArray['user']['first_name'].' '.$orderDetailsArray['user']['last_name'],
            'orderNo'           =>  $orderDetailsArray['orderNo'],
            'orderDate'         =>  $orderDetailsArray['orderDate'],
            'totalAmount'       =>  $orderDetailsArray['orderAmount'],
            'temp_seat_booking' =>  $orderDetailsArray['temp_seat_booking'],
            'order_status'      =>  $orderDetailsArray['order_status'],
            'admin_email'       =>  $setting[25]['options_value'],
            'eventArray'        =>  $eventArr,
            'priceType'=>$setting[14]['options_value'],
            'gst'=>$getGst,
            'tax_amount'=>$orderDetails['tax_amount'],
            'offerPrice'=>$orderDetails['offer_value'],
            'aboutSite'       =>  $setting[13]['options_value'],
            'footerSite'       =>  $setting[10]['options_value'],
            'viewURL'         => env('VIEW_WEB_URL').'/emailview/'.encrypt($orderDetailsArray['orderNo']), 
            'logo'            => env('APP_URL').'/storage/app/public/default.png',
            'moreInfo'        => $moreInfo
        );

        // Mail::send('Email.EventBookingWithEventOrder',  ['data' => $data], function($message) use ($data) {
        //    $message->to($data['user_email'])->subject('Booking Order Confirmation From Rudra');
        //    $message->cc($data['admin_email'])->subject('New Event Booking Order Recived');
        //    $message->from($data['admin_email'],config('app.mail_from_name'));
        // });
        //echo "Email Sent with attachment. Check your inbox.";
        
        return view('Email.EventBookingWithEventOrder', ['data' => $data]);
     }


      /**
     *@Author       : Pradeep Kumar
     *@Description  : Welcome Email to User after registering him self
     *@Created Date : 22 JAN 2020
     */
     
    public static function EventBookingEmail($orderID){
        

        $setting = self::getSetting(); 
        $invoiceArr = app(UserController::class)->getEventOrderInvoice($orderID);
        $getGst = app(CartController::class)->getGst();
        $orderDetails =  $invoiceArr['orderDetails'];

        $orderDetailsArray  = array(
            'orderNo'=>$orderDetails['orderID'],
            'orderDate'=>date('d M Y',strtotime($orderDetails['order_date'])),
            'orderAmount'=>$setting[14]['options_value'].$orderDetails['total_amount'],
            'user'=>array('first_name'=>$orderDetails['shipping_fname'],'last_name'=>$orderDetails['shipping_lname'],'email'=>$orderDetails['shipping_email']),
            'temp_seat_booking'=>$orderDetails['temp_seat_booking'],
            'order_status'=>$orderDetails['order_status']
        );

        
        //Event Booked Array
        if(!empty($orderDetailsArray['temp_seat_booking'])){
            foreach($orderDetailsArray['temp_seat_booking'] as $item){
               
                $Seat = [];
                if(!empty($item['Seat'])){
                    foreach($item['Seat'] as $k=>$val){
                        $Seat[] = $val[0];
                    }
                }

                $eventArr[]= array(
                                    'start_time'=> $item['event_timing']['event_start_time'],
                                    'end_time'  => $item['event_timing']['event_end_time'],
                                    'event_seat'=> $item['event_seat']['SeatType'],
                                    'price'     => $item['event_seat']['Price'],
                                    'Theatre'   => $item['event_seat']['Theatre'],
                                    'Event'     => $item['Event']['title'],
                                    'durration' => $item['Event']['durration'],
                                    'EventImageSrc'=> $item['EventImage']['src'],
                                    'EventImageThumb'=> $item['EventImage']['thumbnail'],
                                    'Seat'=>implode(',',$Seat),
                                    'quantity'=>count($Seat),
                                    'priceType'=>$setting[14]['options_value'],
                                    'eventURL'=>env('SITE_URL').'/day-exp-detail/'.$item['Event']['id'].'-'.$item['event_timing_id']
                                  );
            }
        }

        $moreInfo = array(
            'company_name'=> $setting[17]['options_value'],
            'company_address'=> $setting[16]['options_value'],
            'phone'=> $setting[2]['options_value'],
            'mobile'=> $setting[3]['options_value'],
            'email'=> $setting[1]['options_value'],

            'name'=> $orderDetails['shipping_fname'].' '.$orderDetails['shipping_lname'],
            'address'=>$orderDetails['shipping_address1'].' '.$orderDetails['shipping_address2'],
            'location'=>$orderDetails['shipping_city'].','.$orderDetails['shipping_state'].','.$orderDetails['shipping_pincode'],
            'uphone'=>$orderDetails['shipping_mobile'],
            'uemail'=>$orderDetails['email_address'],
        );



        $data = array(
            'site_url'          =>  config('app.site_url'),
            'user_email'        =>  $orderDetailsArray['user']['email'],
            'user_name'         =>  $orderDetailsArray['user']['first_name'].' '.$orderDetailsArray['user']['last_name'],
            'orderNo'           =>  $orderDetailsArray['orderNo'],
            'orderDate'         =>  $orderDetailsArray['orderDate'],
            'totalAmount'       =>  $orderDetailsArray['orderAmount'],
            'temp_seat_booking' =>  $orderDetailsArray['temp_seat_booking'],
            'order_status'      =>  $orderDetailsArray['order_status'],
            'admin_email'       =>  $setting[25]['options_value'],
            'eventArray'        =>  $eventArr,
            'priceType'=>$setting[14]['options_value'],
            'gst'=>$getGst,
            'tax_amount'=>$orderDetails['tax_amount'],
            'offerPrice'=>$orderDetails['offer_value'],
            'aboutSite'       =>  $setting[13]['options_value'],
            'footerSite'       =>  $setting[10]['options_value'],
            'viewURL'         => env('VIEW_WEB_URL').'/emailview/'.encrypt($orderDetailsArray['orderNo']), 
            'logo'            => env('APP_URL').'/storage/app/public/default.png',
            'moreInfo'        => $moreInfo
        );

        Mail::send('Email.EventBookingWithEventOrder',  ['data' => $data], function($message) use ($data) {
           $message->to($data['user_email'])->subject('Booking Order Confirmation From Rudra');
           $message->cc($data['admin_email'])->subject('New Event Booking Order Recived');
           $message->from($data['admin_email'],config('app.mail_from_name'));
        });
        //echo "Email Sent with attachment. Check your inbox.";
     }



     

      /**
     *@Author       : Pradeep Kumar
     *@Description  : Welcome Email to User after registering him self
     *@Created Date : 22 JAN 2020
     */
     
    public static function MemberShipBookingEmail($orderID){
        

        $setting = self::getSetting(); 
        //$invoiceArr = app(UserController::class)->getEventOrderInvoice($orderID);
        $getGst = app(CartController::class)->getGst();
        $orderDetails =  $invoiceArr['orderDetails'];

        $orderDetailsArray  = array(
            'orderNo'=>$orderDetails['orderID'],
            'orderDate'=>date('d M Y',strtotime($orderDetails['order_date'])),
            'orderAmount'=>$setting[14]['options_value'].$orderDetails['total_amount'],
            'user'=>array('first_name'=>$orderDetails['shipping_fname'],'last_name'=>$orderDetails['shipping_lname'],'email'=>$orderDetails['shipping_email']),
            'temp_seat_booking'=>$orderDetails['temp_seat_booking'],
            'order_status'=>$orderDetails['order_status']
        );

        
        //Event Booked Array
        if(!empty($orderDetailsArray['temp_seat_booking'])){
            foreach($orderDetailsArray['temp_seat_booking'] as $item){
               
                $Seat = [];
                if(!empty($item['Seat'])){
                    foreach($item['Seat'] as $k=>$val){
                        $Seat[] = $val[0];
                    }
                }

                $eventArr[]= array(
                                    'start_time'=> $item['event_timing']['event_start_time'],
                                    'end_time'  => $item['event_timing']['event_end_time'],
                                    'event_seat'=> $item['event_seat']['SeatType'],
                                    'price'     => $item['event_seat']['Price'],
                                    'Theatre'   => $item['event_seat']['Theatre'],
                                    'Event'     => $item['Event']['title'],
                                    'durration' => $item['Event']['durration'],
                                    'EventImageSrc'=> $item['EventImage']['src'],
                                    'EventImageThumb'=> $item['EventImage']['thumbnail'],
                                    'Seat'=>implode(',',$Seat),
                                    'quantity'=>count($Seat),
                                    'priceType'=>$setting[14]['options_value'],
                                    'eventURL'=>env('SITE_URL').'/day-exp-detail/'.$item['Event']['id'].'-'.$item['event_timing_id']
                                  );
            }
        }

        $moreInfo = array(
            'company_name'=> $setting[17]['options_value'],
            'company_address'=> $setting[16]['options_value'],
            'phone'=> $setting[2]['options_value'],
            'mobile'=> $setting[3]['options_value'],
            'email'=> $setting[1]['options_value'],

            'name'=> $orderDetails['shipping_fname'].' '.$orderDetails['shipping_lname'],
            'address'=>$orderDetails['shipping_address1'].' '.$orderDetails['shipping_address2'],
            'location'=>$orderDetails['shipping_city'].','.$orderDetails['shipping_state'].','.$orderDetails['shipping_pincode'],
            'uphone'=>$orderDetails['shipping_mobile'],
            'uemail'=>$orderDetails['email_address'],
        );



        $data = array(
            'site_url'          =>  config('app.site_url'),
            'user_email'        =>  $orderDetailsArray['user']['email'],
            'user_name'         =>  $orderDetailsArray['user']['first_name'].' '.$orderDetailsArray['user']['last_name'],
            'orderNo'           =>  $orderDetailsArray['orderNo'],
            'orderDate'         =>  $orderDetailsArray['orderDate'],
            'totalAmount'       =>  $orderDetailsArray['orderAmount'],
            'temp_seat_booking' =>  $orderDetailsArray['temp_seat_booking'],
            'order_status'      =>  $orderDetailsArray['order_status'],
            'admin_email'       =>  $setting[25]['options_value'],
            'eventArray'        =>  $eventArr,
            'priceType'=>$setting[14]['options_value'],
            'gst'=>$getGst,
            'tax_amount'=>$orderDetails['tax_amount'],
            'offerPrice'=>$orderDetails['offer_value'],
            'aboutSite'       =>  $setting[13]['options_value'],
            'footerSite'       =>  $setting[10]['options_value'],
            'viewURL'         => env('VIEW_WEB_URL').'/emailview/'.encrypt($orderDetailsArray['orderNo']), 
            'logo'            => env('APP_URL').'/storage/app/public/default.png',
            'moreInfo'        => $moreInfo
        );

        Mail::send('Email.EventBookingWithEventOrder',  ['data' => $data], function($message) use ($data) {
           $message->to($data['user_email'])->subject('Booking Order Confirmation From Rudra');
           $message->cc($data['admin_email'])->subject('New Event Booking Order Recived');
           $message->from($data['admin_email'],config('app.mail_from_name'));
        });
        //echo "Email Sent with attachment. Check your inbox.";
     }





     /**
     *@Author       : Pradeep Kumar
     *@Description  : Welcome Email to User after registering him self
     *@Created Date : 22 JAN 2020
     */
     
    public static function TravelBookingEmail($orderID){
        

        $setting = self::getSetting(); 
        $invoiceArr = app(UserController::class)->getTravelOrderInvoice($orderID);
       
       
        $getGst = app(CartController::class)->getGst();
        $orderDetails =  $invoiceArr['orderDetails'][0];
        // echo "<prE>";
        // print_r($orderDetails);
        // die;
        $orderDetailsArray  = array(
            'orderNo'=>$orderDetails['orderID'],
            'orderDate'=>date('d M Y',strtotime($orderDetails['order_date'])),
            'orderAmount'=>$setting[14]['options_value'].$orderDetails['total_amount'],
            'user'=>array('first_name'=>$orderDetails['shipping_fname'],'last_name'=>$orderDetails['shipping_lname'],'email'=>$orderDetails['shipping_email']),
            'itinerary_booking'=>$orderDetails['itinerary_booking'],
            'order_status'=>$orderDetails['order_status']
        );

        
        //Event Booked Array
        if(!empty($orderDetailsArray['itinerary_booking'])){
            foreach($orderDetailsArray['itinerary_booking'] as $item){
               
                $Seat = [];
                if(!empty($item['Seat'])){
                    foreach($item['Seat'] as $k=>$val){
                        $Seat[] = $val[0];
                    }
                }

                $eventArr[]= array(
                                    'start_time'=> $item['itinerary_departure']['start_date'],
                                    'end_time'  => $item['itinerary_departure']['end_date'],
                                    'event_seat'=> '',
                                    'price'     => $item['itinerary_departure']['price'],
                                    'Theatre'   => '',
                                    'Event'     => $item['itinerary']['title'],
                                    'durration' => '0',
                                    'EventImageSrc'=> $item['itinerary']['image'],
                                    'EventImageThumb'=> $item['itinerary']['image'],
                                    'Seat'=>'',
                                    'quantity'=>1,
                                    'priceType'=>$setting[14]['options_value'],
                                    'eventURL'=>env('SITE_URL').'/destinationexpdetails/'.$item['itinerary']['id']
                                  );
            }
        }

        $moreInfo = array(
            'company_name'=> $setting[17]['options_value'],
            'company_address'=> $setting[16]['options_value'],
            'phone'=> $setting[2]['options_value'],
            'mobile'=> $setting[3]['options_value'],
            'email'=> $setting[1]['options_value'],

            'name'=> $orderDetails['shipping_fname'].' '.$orderDetails['shipping_lname'],
            'address'=>$orderDetails['shipping_address1'].' '.$orderDetails['shipping_address2'],
            'location'=>$orderDetails['shipping_city'].','.$orderDetails['shipping_state'].','.$orderDetails['shipping_pincode'],
            'uphone'=>$orderDetails['shipping_mobile'],
            'uemail'=>$orderDetails['email_address'],
        );


        $data = array(
            'site_url'          =>  config('app.site_url'),
            'user_email'        =>  $orderDetailsArray['user']['email'],
            'user_name'         =>  $orderDetailsArray['user']['first_name'].' '.$orderDetailsArray['user']['last_name'],
            'orderNo'           =>  $orderDetailsArray['orderNo'],
            'orderDate'         =>  $orderDetailsArray['orderDate'],
            'totalAmount'       =>  $orderDetailsArray['orderAmount'],
            'temp_seat_booking' =>  $orderDetailsArray['itinerary_booking'],
            'order_status'      =>  $orderDetailsArray['order_status'],
            'admin_email'       =>  $setting[25]['options_value'],
            'eventArray'        =>  $eventArr,
            'priceType'=>$setting[14]['options_value'],
            'gst'=>$getGst,
            'tax_amount'=>$orderDetails['tax_amount'],
            'offerPrice'=>$orderDetails['offer_value'],
            'aboutSite'       =>  $setting[13]['options_value'],
            'footerSite'       =>  $setting[10]['options_value'],
            'viewURL'         => env('VIEW_WEB_URL').'/emailtview/'.encrypt($orderDetailsArray['orderNo']), 
            'logo'            => env('APP_URL').'/storage/app/public/default.png',
            'moreInfo'        => $moreInfo
        );

        Mail::send('Email.TravelBookingWithOrder',  ['data' => $data], function($message) use ($data) {
           $message->to($data['user_email'])->subject('Your Booking Order Confirmation From Rudra');
           $message->cc($data['admin_email'])->subject('New Travel Booking Order Recived');
           $message->from($data['admin_email'],config('app.mail_from_name'));
        });
        //echo "Email Sent with attachment. Check your inbox.";
     }


     /**
     *@Author       : Pradeep Kumar
     *@Description  : Welcome Email to User after registering him self
     *@Created Date : 22 JAN 2020
     */
     
    public static function ViewTravelBookingEmail($encryptOrderId){
        $orderID = decrypt($encryptOrderId); 
        $setting = self::getSetting(); 
        $invoiceArr = app(UserController::class)->getTravelOrderInvoice($orderID);
       
       
        $getGst = app(CartController::class)->getGst();
        $orderDetails =  $invoiceArr['orderDetails'][0];
       
        $orderDetailsArray  = array(
            'orderNo'=>$orderDetails['orderID'],
            'orderDate'=>date('d M Y',strtotime($orderDetails['order_date'])),
            'orderAmount'=>$setting[14]['options_value'].$orderDetails['total_amount'],
            'user'=>array('first_name'=>$orderDetails['shipping_fname'],'last_name'=>$orderDetails['shipping_lname'],'email'=>$orderDetails['shipping_email']),
            'itinerary_booking'=>$orderDetails['itinerary_booking'],
            'order_status'=>$orderDetails['order_status']
        );

        
        //Event Booked Array
        if(!empty($orderDetailsArray['itinerary_booking'])){
            foreach($orderDetailsArray['itinerary_booking'] as $item){
               
                $Seat = [];
                if(!empty($item['Seat'])){
                    foreach($item['Seat'] as $k=>$val){
                        $Seat[] = $val[0];
                    }
                }

                $eventArr[]= array(
                                    'start_time'=> date("d M Y",strtotime($item['itinerary_departure']['start_date'])),
                                    'end_time'  => date("d M Y",strtotime($item['itinerary_departure']['end_date'])),
                                    'event_seat'=> '',
                                    'price'     => $item['itinerary_departure']['price'],
                                    'Theatre'   => '',
                                    'Event'     => $item['itinerary']['title'],
                                    'durration' => '0',
                                    'EventImageSrc'=> $item['itinerary']['image'],
                                    'EventImageThumb'=> $item['itinerary']['image'],
                                    'Seat'=>'',
                                    'quantity'=>1,
                                    'priceType'=>$setting[14]['options_value'],
                                    'eventURL'=>env('SITE_URL').'/destinationexpdetails/'.$item['itinerary']['id']
                                  );
            }
        }

        $moreInfo = array(
            'company_name'=> $setting[17]['options_value'],
            'company_address'=> $setting[16]['options_value'],
            'phone'=> $setting[2]['options_value'],
            'mobile'=> $setting[3]['options_value'],
            'email'=> $setting[1]['options_value'],

            'name'=> $orderDetails['shipping_fname'].' '.$orderDetails['shipping_lname'],
            'address'=>$orderDetails['shipping_address1'].' '.$orderDetails['shipping_address2'],
            'location'=>$orderDetails['shipping_city'].','.$orderDetails['shipping_state'].','.$orderDetails['shipping_pincode'],
            'uphone'=>$orderDetails['shipping_mobile'],
            'uemail'=>$orderDetails['email_address'],
        );


        $data = array(
            'site_url'          =>  config('app.site_url'),
            'user_email'        =>  $orderDetailsArray['user']['email'],
            'user_name'         =>  $orderDetailsArray['user']['first_name'].' '.$orderDetailsArray['user']['last_name'],
            'orderNo'           =>  $orderDetailsArray['orderNo'],
            'orderDate'         =>  $orderDetailsArray['orderDate'],
            'totalAmount'       =>  $orderDetailsArray['orderAmount'],
            'temp_seat_booking' =>  $orderDetailsArray['itinerary_booking'],
            'order_status'      =>  $orderDetailsArray['order_status'],
            'admin_email'       =>  $setting[25]['options_value'],
            'eventArray'        =>  $eventArr,
            'priceType'=>$setting[14]['options_value'],
            'gst'=>$getGst,
            'tax_amount'=>$orderDetails['tax_amount'],
            'offerPrice'=>$orderDetails['offer_value'],
            'aboutSite'       =>  $setting[13]['options_value'],
            'footerSite'       =>  $setting[10]['options_value'],
            'viewURL'         => env('VIEW_WEB_URL').'/emailtview/'.encrypt($orderDetailsArray['orderNo']), 
            'logo'            => env('APP_URL').'/storage/app/public/default.png',
            'moreInfo'        => $moreInfo
        );
        //\PDF::setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
        //$pdf = \PDF::loadView('PDF.test',  $data);
        //return $pdf->download('invoice.pdf');
        return view('Email.TravelBookingWithOrder', ['data' => $data]);
     }



    private static function getSetting(){
        $setting = Setting::all()->toArray();
        return $setting;
    }

}
