<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MasterController;
use Auth;
use App\User;
use Session;
use App\SittingType;
use App\Page;
use App\MembershipPlan;
use App\MembershipFeature;
use App\ReviewVideo;
use App\Order;
use DB;
use App\Event;
use Storage;
use App\BannerGallery;
use App\Setting;
use App\Itinerary;

class GeneralController extends MasterController
{

    public $successStatus = 200;

    public function testAPI(Request $request) {
        $array = ['apiTest'=>'OK'];
        return response()->json($array);
    }



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


    private function getSetting(){
        $setting = Setting::all();
        return $setting;
    }


    
    /*
     *@Get All Dashboard Data
     */
    public function getAllTravellOrderList(Request $request){
        $setting = $this->getSetting();
        $priceType = $setting['14']['options_value'];

        $responseArray = array();
        if($request->isMethod('post')){
            $order = Order::with('ItineraryBooking','OrderStatus')->where('order_type','=',2)->get()->toArray();
            $responseArray['order']=array("orderList"=>$order,'Count'=>count($order));

            /****************Datatable Start***************/
                $columns = array(
                    array('label'=>'SN','field'=>'id','sort'=>'asc','width'=>'25'),
                    array('label'=>'orderID','field'=>'orderID','sort'=>'asc','width'=>'170'),
                    array('label'=>'Order Date','field'=>'order_date','sort'=>'asc','width'=>'100'),
                    array('label'=>'Name','field'=>'shipping_fname','sort'=>'asc','width'=>'100'),
                    array('label'=>'Email','field'=>'shipping_email','sort'=>'asc','width'=>'100'),
                    array('label'=>'Contact Number','field'=>'shipping_mobile','sort'=>'asc','width'=>'100'),
                    array('label'=>'Total Amount','field'=>'total_amount','sort'=>'asc','width'=>'100'),
                    array('label'=>'Offer Applied','field'=>'is_offer_applied','sort'=>'asc','width'=>'100'),
                    array('label'=>'Order Status','field'=>'order_status','sort'=>'asc','width'=>'100'),
                    array('label'=>'Created On','field'=>'created_at','sort'=>'asc','width'=>'100'),
                    );
                $row = [];
                $actionStr ='';
                foreach($order as $item){
                    $row[] =array(
                        'id'=>$item['id'],
                        'orderID'=>($item['orderID']!='')?$item['orderID']:"--",
                        'order_date'=>date("d-M-Y",strtotime($item['order_date'])), 
                        'shipping_fname'=>($item['shipping_fname']!='')?$item['shipping_fname'].' '.$item['shipping_lname']:"--",
                        'shipping_email'=>($item['shipping_email']!='')?$item['shipping_email']:"--",
                        'shipping_mobile'=>($item['shipping_mobile']!='')?$item['shipping_mobile']:"--",
                        'total_amount'=>($item['total_amount']!='')?$priceType.$item['total_amount']:$priceType."0.00",
                        'is_offer_applied'=>($item['is_offer_applied']!='')?$item['is_offer_applied']:"--",
                        'order_status'=>(!empty($item['order_status']))?$item['order_status']['status_type']:"--",
                        'created_at'=>date("d-M-Y",strtotime($item['created_at']))
                    );
                }
                $dataTable = array();
                $dataTable['columns'] =$columns;
                $dataTable['rows'] =$row;
                $responseArray['order']['dataTable']=$dataTable;
            /****************Datatable Ends now***************/
            
            $responseArray['status'] = 'success';
            $responseArray['code'] = '200';
            
        }
        return response()->json($responseArray, $this->successStatus); 
    }



    /*
     *@Get All Dashboard Data
     */
    public function getAllDashobardList(Request $request){
        $setting = $this->getSetting();
        $priceType = $setting['14']['options_value'];

        $responseArray = array();
        if($request->isMethod('post')){
            $order = Order::with('OrderStatus')->where("order_type",'=',1)->get()->toArray();
            $etravelOrderChart=array();
            $etravelOrderAmount = [];
            $esuccess = 0;
            $efailed = 0;
            $ehold = 0;
            $enew = 0;
            $ecancel=0;
            $eppending=0;
            foreach($order as $item){
                if($item['order_status_id']==1){ 
                    $esuccess++;
                    $etravelOrderChart["paymentSccuess"]=$esuccess;
                }else if($item['order_status_id']==5){ 
                    $efailed++;
                    $etravelOrderChart['failed']=$efailed;
                }else if($item['order_status_id']==3){ 
                    $ehold++;
                    $etravelOrderChart["onHold"]=$ehold;
                }else if($item['order_status_id']==4){ 
                    $eppending++;
                    $etravelOrderChart["paymentPending"]=$eppending;
                }else if($item['order_status_id']==6){ 
                    $ecancel++;
                    $etravelOrderChart["cancel"]=$ecancel;
                }else{
                    $enew++;
                    $etravelOrderChart["New"]=$enew;
                }
                $etravelOrderAmount[]=$item['total_amount'];
            }

            $responseArray['order']=array("orderList"=>$order,'Count'=>count($order));

            /****************Datatable Start***************/
            $columns = array(
                array('label'=>'SN','field'=>'id','sort'=>'asc','width'=>'25'),
                array('label'=>'orderID','field'=>'orderID','sort'=>'asc','width'=>'170'),
                array('label'=>'Order Date','field'=>'order_date','sort'=>'asc','width'=>'100'),
                array('label'=>'Name','field'=>'shipping_fname','sort'=>'asc','width'=>'100'),
                array('label'=>'Email','field'=>'shipping_email','sort'=>'asc','width'=>'100'),
                array('label'=>'Contact Number','field'=>'shipping_mobile','sort'=>'asc','width'=>'100'),
                array('label'=>'Total Amount','field'=>'total_amount','sort'=>'asc','width'=>'100'),
                array('label'=>'Offer Applied','field'=>'is_offer_applied','sort'=>'asc','width'=>'100'),
                array('label'=>'Order Status','field'=>'order_status','sort'=>'asc','width'=>'100'),
                array('label'=>'Created On','field'=>'created_at','sort'=>'asc','width'=>'100'),
                );
            $row = [];
            $actionStr ='';
            foreach($order as $item){
                $row[] =array(
                    'id'=>$item['id'],
                    'orderID'=>($item['orderID']!='')?$item['orderID']:"--",
                    'order_date'=>date("d-M-Y",strtotime($item['order_date'])), 
                    'shipping_fname'=>($item['shipping_fname']!='')?$item['shipping_fname'].' '.$item['shipping_lname']:"--",
                    'shipping_email'=>($item['shipping_email']!='')?$item['shipping_email']:"--",
                    'shipping_mobile'=>($item['shipping_mobile']!='')?$item['shipping_mobile']:"--",
                    'total_amount'=>($item['total_amount']!='')?$priceType.$item['total_amount']:$priceType."0.00",
                    'is_offer_applied'=>($item['is_offer_applied']!='')?$item['is_offer_applied']:"--",
                    'order_status'=>(!empty($item['order_status']))?$item['order_status']['status_type']:"--",
                    'created_at'=>date("d-M-Y",strtotime($item['created_at']))
                );
            }
            $dataTable = array();
            $dataTable['columns'] =$columns;
            $dataTable['rows'] =$row;
            $responseArray['order']['dataTable']=$dataTable;
        /****************Datatable Ends now***************/

            //Get Total Sum Of Booking Amount
            $total = DB::table("orders")
                    ->select(DB::raw("SUM(total_amount) as total"))
                    ->groupBy(DB::raw("year(created_at)"))
                    ->get();

            //All Users
            $users = User::latest('id')
                        ->limit(12)->get();



            //all latest event added into system
            $eventItem = array();
            $event = Event::with('EventDetail','EventGallery')->orderBy('id', 'ASC') ->limit(10)->get()->toArray();

            //print_r($event);die;
            foreach($event as $item){
                if(!empty($item['event_detail'])){
                    if(!empty($item['event_detail'][0]['event_timing'])){
                        $eventItem[]=$item;
                    }
                }
            }

            //Formate Event Array
            $eventFinalArr = array();
            foreach ($eventItem as $value) {
                $eventFinalArr[]=array(
                    'event_id'=>$value['id'],
                    'durration'=>$value['durration'],
                    'id'=>$value['event_detail'][0]['event_timing'][0]['id'],
                    'title'=>$value['event_detail'][0]['event_timing'][0]['theatre']['theater_name'],
                    'place'=>$value['event_detail'][0]['city']['city_name'],
                    'price'=>$priceType.$value['event_detail'][0]['event_timing'][0]['price'][0]['price'],
                    'image'=>$this->getEventImage($value['event_gallery']),
                );
            }
            //[["Type","Value"], ["New", 5], ["Booked", 25], ["Payment Sccuess", 52], ["Cancel",  52]]
            $travelOrderChart=array();
            $travelOrderAmount = [];
            $success = 0;
            $failed = 0;
            $hold = 0;
            $new = 0;
            $cancel=0;
            $ppending=0;
            $travelOrder = Order::with('OrderStatus')->where("order_type",'=',2)->get()->toArray();
            foreach($travelOrder as $item){
                if($item['order_status_id']==1){ 
                    $success++;
                    $travelOrderChart["paymentSccuess"]=$success;
                }else if($item['order_status_id']==5){ 
                    $failed++;
                    $travelOrderChart['failed']=$failed;
                }else if($item['order_status_id']==3){ 
                    $hold++;
                    $travelOrderChart["onHold"]=$hold;
                }else if($item['order_status_id']==4){ 
                    $ppending++;
                    $travelOrderChart["paymentPending"]=$ppending;
                }else if($item['order_status_id']==6){ 
                    $cancel++;
                    $travelOrderChart["cancel"]=$cancel;
                }else{
                    $new++;
                    $travelOrderChart["New"]=$new;
                }
                $travelOrderAmount[]=$item['total_amount'];
            }

            $responseArray['order']['Enquiry'] = 0;
            $responseArray['order']['Users'] = $users->count();
            $responseArray['order']['TravelOrder'] = count($travelOrder);
            $responseArray['order']['travelOrderChart'] = $travelOrderChart;
            $responseArray['order']['etravelOrderChart'] = $etravelOrderChart;
            
            $responseArray['order']['TravelOrderAmount'] =number_format(array_sum($travelOrderAmount),2);
            $responseArray['order']['UsersList'] = $users;
            $responseArray['order']['TotalAmount'] = number_format($total[0]->total,2);
            $responseArray['order']['EventList'] = $eventFinalArr;
            $responseArray['order']['Settings'] = $setting;


            $responseArray['status'] = 'success';
            $responseArray['code'] = '200';
            
        }
        return response()->json($responseArray, $this->successStatus); 
    }




    public function addseat(Request $request){
        if($request->isMethod('post'))
        {
            //print_r($request->all());
            $sittingType = new SittingType();
            $sittingType->sitting_type_name = $request->get('title');
            $sittingType->status = $request->get('status');
            if($sittingType->save()){
                $responseArray['status'] = 'success';
                $responseArray['code'] = '200';
                $responseArray['message'] = 'Seating Type Added Successfully.';
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




    public function getseattinglist(Request $request){
        if($request->isMethod('post'))
        {
            if($request->get('id')>0){
                $id = $request->get('id');
                $sittingType = SittingType::find($id);
            }else{
                $sittingType = SittingType::get();
            }
            $responseArray['status'] = 'success';
            $responseArray['code'] = '200';
            $responseArray['sitting'] = $sittingType;
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['message'] = 'Invalid Requets';

        }
        return response()->json($responseArray, $this->successStatus); 

    }




    public function updateseat(Request $request){
        if($request->isMethod('post'))
        {
            $id = $request->get('id');
            $sittingType = SittingType::find($id);
            $sittingType->sitting_type_name = $request->get('title');
            $sittingType->status = $request->get('status');
            if($sittingType->save()){
                $responseArray['status'] = 'success';
                $responseArray['code'] = '200';
                $responseArray['message'] = 'Seating Type Updated Successfully.';
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



    public function getpagelist(Request $request){

        if($request->isMethod('post'))
        {
            $pageList = Page::all();
            if($pageList->count()>0)
                $responseArray['status'] = 'success';
                $responseArray['code'] = '200';
                $responseArray['page'] = $pageList;
            }else{
                $responseArray['status'] = 'error';
                $responseArray['code'] = '500';
                $responseArray['message'] = 'Somthing went wrong!! Please try after sometime';
            }
            return response()->json($responseArray, $this->successStatus); 
    }



    public function getpagedetails(Request $request){
        if($request->isMethod('post'))
        {
            $id = $request->get('id');
            $pageList = Page::find($id);
            if(!empty($pageList))
                $responseArray['status'] = 'success';
                $responseArray['code'] = '200';
                $responseArray['page'] = $pageList;
            }else{
                $responseArray['status'] = 'error';
                $responseArray['code'] = '500';
                $responseArray['message'] = 'Somthing went wrong!! Please try after sometime';
            }
            return response()->json($responseArray, $this->successStatus); 

    }



    public function pagedetailupdate(Request $request){
        $responseArray = array();
        if($request->isMethod('post'))
        {
            $id          = $request->get('id');
            $page_name   = $request->get('title');
            $description = $request->get('description');
            $slug        = $request->get('slug');
            $status      = $request->get('status');
            $pageList = Page::find($id);
            if(!empty($pageList)){
                $pageList->page_name    = $page_name;
                $pageList->description  = $description;
                $pageList->slug         = $slug;
                $pageList->status       = $status;
                if($pageList->save()){
                    $responseArray['status'] = 'success';
                    $responseArray['code'] = '200';
                    $responseArray['message'] = "Page Infomration updated.";
                }else{
                    $responseArray['status'] = 'success';
                    $responseArray['code'] = '500';
                    $responseArray['message'] = 'Somthing went wrong!! Please try after sometime';
                }
           }else{
                $responseArray['status'] = 'error';
                $responseArray['code'] = '500';
                $responseArray['message'] = 'Somthing went wrong!! Please try after sometime';
            }
            return response()->json($responseArray, $this->successStatus); 
        }
    }


    /*
     * @Membership List API
     * @createdOn : 07 June 2019
     */
    public function getMembership(Request $request){
        $membership = MembershipPlan::with('MembershipFeature')->get();
        if(!empty($membership)){
            $responseArray['status'] = 'success';
            $responseArray['code'] = '200';
            $responseArray['membership'] = $membership;

        }else{
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['message'] = 'Somthing went wrong!! Please try after sometime';
            $this->successStatus = 500;
        }
        return response()->json($responseArray, $this->successStatus);


    }




    /*
     * @New Review Viedos API
     * @createdOn : 11 June 2019
     */
    public function updateViedos(Request $request){
        if($request->isMethod('post'))
        {
            $id         = $request->get('id');
            $title      = $request->get('title');
            $url        = $request->get('url');
            $status     = $request->get('status');
            if($id>0){
                $pageList = ReviewVideo::find($id);
            }else{
                $pageList = new ReviewVideo();
            }
            $pageList->title    = $title;
            $pageList->url      = $url;
            $pageList->status   = $status;

            if($id!=''){
                $validator = Validator::make($request->all(), [
                    'title' => 'required|unique:review_videos,title,'.$id,
                    'url'=> 'required',
                ]);
            }else{

                $validator = Validator::make($request->all(), [
                    'title' => 'required|unique:review_videos|max:255',
                    'url'=> 'required',
                ]);
            }
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

                if($pageList->save()){
                    $responseArray['status'] = 'success';
                    $responseArray['code'] = '200';
                    $responseArray['message'] = "Viedos Infomration updated.";
                }else{
                    $responseArray['status'] = 'success';
                    $responseArray['code'] = '500';
                    $responseArray['message'] = 'Somthing went wrong!! Please try after sometime';
                }
            }
            return response()->json($responseArray, $this->successStatus); 
        }
    }








    /*
     * @New Review Viedos API
     * @createdOn : 11 June 2019
     */
    public function getViedoList(Request $request){
        if($request->isMethod('post'))
        {
            $list = ReviewVideo::orderBy('id','DESC')->get();
            if($list){
                $responseArray['status'] = 'success';
                $responseArray['code'] = '200';
                $responseArray['list'] = $list;
            }else{
                $responseArray['status'] = 'success';
                $responseArray['code'] = '500';
                $responseArray['message'] = 'Somthing went wrong!! Please try after sometime';
            }
            return response()->json($responseArray, $this->successStatus); 
        }
    }


    

     /*
     * @New Review Viedos API
     * @createdOn : 11 June 2019
     */
    public function deleteViedos(Request $request){
        if($request->isMethod('post'))
        {
            $id         = $request->get('id');
            if($id>0){
                $pageList = ReviewVideo::find($id);
                if($pageList->delete()){
                    $responseArray['status'] = 'success';
                    $responseArray['code'] = '200';
                    $responseArray['message'] = "Viedos Infomration updated.";
                }else{
                    $responseArray['status'] = 'error';
                    $responseArray['code'] = '500';
                    $responseArray['message'] = 'Somthing went wrong!! Please try after sometime';
                }
            }else{
                $responseArray['status'] = 'error';
                $responseArray['code'] = '500';
                $responseArray['message'] = 'Somthing went wrong!! Please try after sometime';

            }
            return response()->json($responseArray, $this->successStatus); 
        }
    }




    
    //Upload Image Of the event
    public function imageupload(Request $request){
       $id= $request->get('id');
       $responseArray = array();
       if($request->get('imageStr')){
        foreach($request->get('imageStr') as $file){
                list($type, $data) = explode(';', $file);
                list(, $data)      = explode(',', $file);
                $datas = base64_decode($data);
                $typeArr = explode('/', $type);
                $file = md5(uniqid()) . '.'.end($typeArr);
               
                Storage::disk('banner')->put($file, base64_decode($data));
                if($this->saveBannerImage($id,$file)){
                    /*
                    src: "https://c2.staticflickr.com/9/8356/28897120681_3b2c0f43e0_b.jpg",
                    thumbnail: "https://c2.staticflickr.com/9/8356/28897120681_3b2c0f43e0_n.jpg",
                    thumbnailWidth: 320,
                    thumbnailHeight: 212,
                    tags: [{value: "Ocean", title: "Ocean"}, {value: "Peoplessssss", title: "People"}],
                    caption: "Boats (Jeshu John - designerspics.com)"
                    */
                    $imageArr[]=array('status'=>true,'image'=>$file);
                }
        }
        if(count($imageArr)>0){
                $responseArray['status'] = true;
                $responseArray['code']= "200";
                $responseArray['message']= "Image updated Successfully!!";
                $responseArray['images']= $imageArr;
            }else{
                $responseArray['status'] = false;
                $responseArray['code']= "500";
                $responseArray['message']= "Opps! Somthing went wrong";
                return response()->json(['data' => $responseArray], $this->successStatus); 
            }
            
        }

        //Get Image List of this Event
        $imageList = BannerGallery::where('banner_type_id','=',1)->orderBy('id','DESC')->get();
        $imgGalleryList = array();
        foreach($imageList as $item){
            $imgGalleryList[] = array(
                "src"=>env('APP_URL').'/storage/app/public/banner/'.$item['image'],
                "thumbnail"=>env('APP_URL').'/storage/app/public/banner/'.$item['image'],
                "thumbnailWidth"=>rand(250,375),
                "thumbnailHeight"=>rand(175,250),
                "caption"=>"",
                "id"=>$item['id'],
                "is_default"=>$item['is_default'],
                "status"=>$item['status'],
            );
        }
       $responseArray['status'] = true;
       $responseArray['code']= "200";
       $responseArray['imagesList'] = $imgGalleryList;
       return response()->json(['data' => $responseArray], $this->successStatus); 
    }


    private function saveBannerImage($id,$imageName){
        $eventGallery = new BannerGallery();
        $eventGallery['banner_type_id'] = 1;
        $eventGallery['image'] = $imageName;
        $eventGallery['image_thumb'] = $imageName;
        $eventGallery['media_type'] = 'image';
        $eventGallery['status'] = '1';
        $eventGallery['created_at'] = self::getCreatedDate();
        if($eventGallery->save()){
            $lastId= $eventGallery->id;
            $this->resizeImage($lastId);
            return $eventGallery->id;
        }else{
            return false;
        }
    }

    private function resizeImage($id){
        $imageArr = BannerGallery::find($id);
        $imageUrl = env('APP_URL').'/storage/app/public/banner/'.$imageArr['image'];
        $resize = \Image::make($imageUrl);
        // resize image to fixed size 75X68
        $resize->resize(2000,716)->save($imageArr['image']);
        Storage::disk('banner/resize/2000X716')->put($imageArr['image'], $resize);
    }




    

  public function deleteBannerImage(Request $request){
         $id = $request->get('id');
         $eventImage = BannerGallery::find($id);
         if($eventImage->delete()){
             $responseArray['status'] = true;
             $responseArray['code']= "200";
             $responseArray['message']= "Image deleted Successfully!!";
         }else{
             $responseArray['status'] = false;
             $responseArray['code']= "500";
             $responseArray['message']= "Image not deleted !!";
         }
         return response()->json(['data' => $responseArray], $this->successStatus); 
  }



    public function defaultBannerImage(Request $request){
    
         $id = $request->get('id');
         $eventImage = BannerGallery::find($id);
         DB::table('banner_galleries')->update(['is_default' => 0]);
         if($eventImage->is_default==0){
            $eventImage->is_default = 1;
         }else{
            $eventImage->is_default = 0;
         }
         if($eventImage->save()){
             $responseArray['status'] = true;
             $responseArray['code']= "200";
             $responseArray['message']= "Image set as default Successfully!!";
         }else{
             $responseArray['status'] = false;
             $responseArray['code']= "500";
             $responseArray['message']= "Image not set as default !!";
         }
         return response()->json(['data' => $responseArray], $this->successStatus); 
    
    }




    

    public function updateBannerImageStatus(Request $request){
    
         $id = $request->get('id');
         $eventImage = BannerGallery::find($id);
         if($eventImage->status==0){
            $eventImage->status = 1;
            $msg = "Image status set as active !!";
         }else{
            $eventImage->status = 0;
            $msg = "Image status set as inactive !!";
         }
         if($eventImage->save()){
             $responseArray['status'] = true;
             $responseArray['code']= "200";
             $responseArray['message']= $msg;
         }else{
             $responseArray['status'] = false;
             $responseArray['code']= "500";
             $responseArray['message']= "Image status not updated !!";
         }
         return response()->json(['data' => $responseArray], $this->successStatus); 
    
    }












}
