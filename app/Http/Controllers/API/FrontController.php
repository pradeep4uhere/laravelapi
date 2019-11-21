<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MasterController;
use Auth;
use App\User;
use Session;
use App\Event;
use App\SittingType;
use App\Page;
use App\MembershipPlan;
use App\MembershipFeature;
use App\ReviewVideo;
use App\Setting;
use App\EventTiming;
use App\Destination;
use App\BannerGallery;
use App\Itinerary;
use App\ItineraryDay;
use App\ItineraryDayGallery;
use App\ItineraryGallery;
use App\City;
use App\State;


class FrontController extends MasterController
{

    public $successStatus = 200;

    public $resize = false;

    function __construct() {
        $this->resize = false; 
    }

    public function testAPI(Request $request) {
        $array = ['apiTest'=>'OK'];
        return response()->json($array);
    }

    

    public function getAllCity(Request $request){
        try{
            
            $id= $request->get('id');
            if($id!=''){
                $cityArr = City::where('state_id','=',$id)->where('status','=',1)->get()->toArray();
            }else{
                $cityArr = City::where('status','=',1)->get()->toArray();
            }
            //Get all Review List
            $responseArray['status'] = true;
            $responseArray['code'] = 200;
            $responseArray['cityList'] = $cityArr;
            
        }catch (Exception $e) {
            $responseArray['status'] = false;
            $responseArray['code'] = 500;
            $responseArray['message'] = $e->getMessage();
        }
        return response()->json($responseArray);
    }



    public function getAllState(Request $request){
        try{
            
            $id= $request->get('id');
            if($id!=''){
                $stateArr = State::find($id);
            }else{
                $stateArr = State::where('status','=',1)->get()->toArray();
            }
            //Get all Review List
            $responseArray['status'] = true;
            $responseArray['code'] = 200;
            $responseArray['stateList'] = $stateArr;
            
        }catch (Exception $e) {
            $responseArray['status'] = false;
            $responseArray['code'] = 500;
            $responseArray['message'] = $e->getMessage();
        }
        return response()->json($responseArray);
    }





    private function getEventImage($event_gallery){
    	if(!empty($event_gallery)){
    		$count=1;
    		foreach ($event_gallery as $key => $value) {
    			if($value['is_default']==1){
    				return env('APP_URL').'/storage/app/public/event/'.$value['image'];
    			}
    		}
    	}
    }



    private function getEventFeatureImage($event_gallery){
    	if(!empty($event_gallery)){
            $count=1;
            $isFeature = false;
    		foreach ($event_gallery as $key => $value) {
    			if($value['is_feature']==1){
                    $isFeature = true;
    				return env('APP_URL').'/storage/app/public/event/resize/683X739/'.$value['image'];
    			}
            }
            if($isFeature===false){
                foreach ($event_gallery as $key => $value) {
                    if($value['is_default']==1){
                        return env('APP_URL').'/storage/app/public/event/'.$value['image'];
                    }
                }   
            }
    	}
    }


    private function getSetting(){
        $setting = Setting::all();
        return $setting;
    }

    public function popularEventList(Request $request){
        if($request->isMethod('post'))
        {

            $setting = $this->getSetting();
            $priceType = $setting['14']['options_value'];
            $eventItem = array();
            $event = Event::with('EventDetail','EventGallery')->where('status','=',1)->orderBy('id', 'desc')->get()->toArray();
           
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
                // echo "<pre>";
                // print_r($value);
                // die;
                $eventFinalArr[]=array(
                    'event_id'=>$value['id'],
                    'id'=>$value['event_detail'][0]['event_timing'][0]['id'],
                    'title'=>$value['event_detail'][0]['event']['title'],
                    //'title'=>$value['event_detail'][0]['event_timing'][0]['theatre']['theater_name'],
                    //'place'=>$value['event_detail'][0]['city']['city_name'],
                    'place'=>$this->getCityNameById($value['event_detail'][0]['event_timing'][0]['theatre']['city_id']),
                    'price'=>$priceType.$value['event_detail'][0]['event_timing'][0]['price'][0]['price'],
                    'image'=>$this->getEventImage($value['event_gallery']),
                );
            }
            
            $chunk =array_chunk($eventFinalArr,4);
            $finalItem = array();
            foreach($chunk as $key=>$value){
                $finalItem['item_'.$key] = $value;
            }
            // print_r($finalItem);
            // die;
            $responseArray['status'] = 'success';
            $responseArray['event'] = $chunk;
            $responseArray['code'] = '200';

        }else{
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['message'] = 'Invalid Requets';

        }
        return response()->json($responseArray, $this->successStatus); 
    }


    public function getCityName(Request $request){
        try{
            $id= $request->get('id');
            $cityArr = City::find($id);

            //Get all Review List
            $responseArray['status'] = true;
            $responseArray['code'] = 200;
            $responseArray['data'] = $cityArr;
            
        }catch (Exception $e) {
            $responseArray['status'] = false;
            $responseArray['code'] = 500;
            $responseArray['message'] = $e->getMessage();
        }
        return response()->json($responseArray);
    }


    public function getCityNameById($id){
        $cityArr = City::find($id);
        return $cityArr['city_name'];
    }


    public function getSettingList(Request $request){
        try{
            $settingArr = Setting::all();

            //Get all Review List
            $reviewVideos = ReviewVideo::where('status','=',1)->limit(3)->get();
            $allreviewVideos = ReviewVideo::where('status','=',1)->get();
            $responseArray['status'] = true;
            $responseArray['code'] = 200;
            $responseArray['data'] =array('setting'=>$settingArr,'review'=>$reviewVideos,'allreview'=>$allreviewVideos);
            
        }catch (Exception $e) {
            $responseArray['status'] = false;
            $responseArray['code'] = 500;
            $responseArray['message'] = $e->getMessage();
        }
        return response()->json($responseArray);
    }


    /****Get Event Details Page********/
    public function getEventDetails(Request $request){
        try{
            $requestData = $request->all();
            $id = $request->get('id');
            if($id!=''){
                $idStr = explode('-',$id);
                $eventId = $idStr[0];
                $eventTimingId = $idStr[1];
            }
            $eventDetails = Event::with('EventDetail','EventGallery')->where('id','=',$id)->where('status','=',1)->orderBy('id', 'desc')->get()->toArray();

            //Select Event Timing What User Clicked On Event e.g Timing ID 
            if(!empty($eventDetails)){
                $gallery = $eventDetails[0]['event_gallery'];
                //storage/app/public/event/4003590a7e9a52292ca2526a74a58a11.jpeg
                if(!empty($eventDetails[0]['event_gallery'])){
                    foreach($eventDetails[0]['event_gallery'] as $imageItem){
                        $url = env('APP_URL').'/storage/app/public/event/resize/1139X627/'.$imageItem['image_thumb'];
                        $durl = env('APP_URL').'/storage/app/public/event/resize/372X253/'.$imageItem['image_thumb'];
                        $image[]=array(
                                'original'=>"$url",
                                'thumbnail'=>"$durl"
                        );
                    }
                }else{
                    $image = array();
                }

                if(!empty($eventDetails[0]['event_detail'])){
                    foreach($eventDetails[0]['event_detail'][0]['event_timing'] as $key=>$item){
                        if($item['id']==$eventTimingId){
                            $eventTiming = $item;
                        }
                    }
                }
                $eid = $eventDetails[0]['id'];
                $title = $eventDetails[0]['title']; 
                $durration = $eventDetails[0]['durration']; 
                $description = $eventDetails[0]['description']; 
                $long_description = $eventDetails[0]['long_description'];
                $banner = env('APP_URL').'/storage/app/public/event/'.$eventDetails[0]['banner'];
                $eventParent = array('id'=>$eid, 'title'=>$title, 'durration'=>$durration,'description'=>$description,'banner'=>$banner,'long_description'=>$long_description);
            }else{
                $eventParent = array();
                $eventTiming = array();
                $image       = array();
            }
            

            $responseArray['status'] = true;
            $responseArray['code'] = 200;
            $responseArray['data'] =array(
                'event'=>$eventParent,
                'event_time'=>$eventTiming,
                'gallery'=>$image
            );
            
        }catch (Exception $e) {
            $responseArray['status'] = false;
            $responseArray['code'] = 500;
            $responseArray['message'] = $e->getMessage();
        }
        return response()->json($responseArray);
    }





    public function getDestinationList(Request $request){
        try{
            $all = $request->all();
            $type = $request->get('type');
            if($type=='short'){
                $typeSort = 'ASC';
            }else if($type=='long'){
                $typeSort = 'DESC';
            }else{
                $typeSort = 'ASC';
            }
            if(array_key_exists('id', $all) && !empty($request->get('id'))){
                $id = $request->get('id'); 
                $settingArr = Destination::with('DestinationGallery')->where('status','=',1)->where('id','=',$id)->get()->toArray();
                //print_r($settingArr[0]['destination_gallery']);die;
                if(!empty($settingArr[0]['destination_gallery'])){
                        foreach($settingArr[0]['destination_gallery'] as $k=>$v){
                             if($this->resize){
                                 $url = env('APP_URL').'/storage/app/public/destination/resize/1139X627/'.$v['image'];
                                 $url2 = env('APP_URL').'/storage/app/public/destination/resize/372X253/'.$v['image'];
                                 $url3 = env('APP_URL').'/storage/app/public/destination/resize/75X68/'.$v['image'];
                             }else{
                                 $url = env('APP_URL').'/storage/app/public/destination/'.$v['image'];
                                 $url2 = env('APP_URL').'/storage/app/public/destination/'.$v['image'];
                                 $url3 = env('APP_URL').'/storage/app/public/destination/'.$v['image'];
                             }
                             if($v['is_default']==1){
                                 $settingArr[0]['destination_gallery'][0]=array('original'=>$url,'thumbnail'=>$url2,'icon'=>$url3);    
                             }
                                 $settingArr[0]['destination_gallery'][$k]=array('original'=>$url,'thumbnail'=>$url2,'icon'=>$url3);
                            
                        }     
                    }else{
                        $settingArr[0]['defaultImg']= env('APP_URL').'/storage/app/public/default/rudra.png';
                    } 
            }else{
                $settingArr = Destination::with('DestinationGallery')->where('status','=',1)->orderBy('trip_type',$typeSort)->get()->toArray();
                //set All the Image Path 
               
                foreach($settingArr as $key=>$value){
                    
                    if(!empty($value['destination_gallery'])){
                        foreach($value['destination_gallery'] as $k=>$v){
                            $url = env('APP_URL').'/storage/app/public/destination/resize/372X253/'.$v['image'];
                            $url3 = env('APP_URL').'/storage/app/public/destination/resize/75X68/'.$v['image'];
                            if($v['is_default']==1){
                             $settingArr[$key]['destination_gallery'][0]=array('icon'=>$url3,'image'=>$url);
                            }
                            $settingArr[$key]['destination_gallery'][$k]=array('icon'=>$url3,'image'=>$url);
                            
                        }    
                    }else{
                        $settingArr[$key]['defaultImg']= env('APP_URL').'/storage/app/public/default/rudra.png';
                    }        
                }
            }
            $globalArr = Setting::all();
            $responseArray['status'] = true;
            $responseArray['code'] = 200;
            $responseArray['data'] =$settingArr;
            $responseArray['setting'] =$globalArr;
            
        }catch (Exception $e) {
            $responseArray['status'] = false;
            $responseArray['code'] = 500;
            $responseArray['message'] = $e->getMessage();
        }
        return response()->json($responseArray);

    }



    /*
    *Get All the Destination Exp List
    */
    public function getDestinationExpList(Request $request){
        try{
            $all = $request->all();
            $type = $request->get('type');
            $typeSort = 'ASC';
            if($type=='long'){$typeSort = 'DESC';}
            
            
            if(array_key_exists('id', $all) && !empty($request->get('id'))){
                $id = $request->get('id'); 
                $settingArr = Itinerary::with('ValidItineraryDeparture','ItineraryAddon','ItineraryTermsAndConditions','ItineraryDay','ItineraryGallery')->where('status','=',1)->where('id','=',$id)->get()->toArray();
                //print_r($settingArr[0]['destination_gallery']);die;
                if(!empty($settingArr[0]['itinerary_gallery'])){
                        foreach($settingArr[0]['itinerary_gallery'] as $k=>$v){
                             $url = env('APP_URL').'/storage/app/public/itinerary/'.$v['image'];
                             $settingArr[0]['itinerary_gallery'][$k]=array('original'=>$url,'thumbnail'=>$url);
                        }  
                         
                }else{
                    $settingArr['defaultImg']= env('APP_URL').'/storage/app/public/default/rudra.png';
                }

                foreach($settingArr[0]['itinerary_day'] as $kkk=>$daysItem){
                    if(!empty($daysItem['itinerary_day_gallery'])){
                        foreach($daysItem['itinerary_day_gallery'] as $kk=>$vv){
                            $daysImageurl = env('APP_URL').'/storage/app/public/itineraryday/'.$vv['image'];
                            $settingArr[0]['itinerary_day'][$kkk]['itinerary_day_gallery'][$kk]=array(
                                'original'=>$daysImageurl,
                                'thumbnail'=>$daysImageurl,
                            );
                        }    
                    }else{
                        $settingArr['defaultImg']= env('APP_URL').'/storage/app/public/default/rudra.png';
                    }
                    
                }
                $itinerary =$settingArr; 

            }else{
                $itinerary = [];
                $settingArr = Itinerary::with('ItineraryDay','ItineraryGallery')->where('status','=',1)->orderBy('trip_type',$typeSort)->get()->toArray();
                //echo "<pre>";
                //print_r($settingArr);die;
                //set All the Image Path 
                foreach($settingArr as $key=>$value){
                    if(!empty($value['itinerary_gallery'])){
                        foreach($value['itinerary_gallery'] as $k=>$v){
                            $url = env('APP_URL').'/storage/app/public/itinerary/'.$v['image'];
                             $settingArr[$key]['itinerary_gallery'][$k]=$url;
                        }  
                        $itinerary[] =$settingArr[$key];

                    }       
                }
            }
            $globalArr = Setting::all();
            $responseArray['status'] = true;
            $responseArray['code'] = 200;
            $responseArray['data'] =$itinerary;
            $responseArray['setting'] =$globalArr;
            
        }catch (Exception $e) {
            $responseArray['status'] = false;
            $responseArray['code'] = 500;
            $responseArray['message'] = $e->getMessage();
        }
        return response()->json($responseArray);

    }




    public function getBannerList(Request $request){
        try{
            $all = $request->all();
            $globalArr = Setting::all();

            $priceType = $globalArr['14']['options_value'];
            $eventItem = array();
            $event = Event::with('EventDetail','EventGallery')->where('status','=',1)->orderBy('id', 'desc')->limit(7)->get()->toArray();
           
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
                    'id'=>$value['event_detail'][0]['event_timing'][0]['id'],
                    'title'=>$value['event_detail'][0]['event_timing'][0]['theatre']['theater_name'],
                    'place'=>$value['event_detail'][0]['city']['city_name'],
                    'price'=>$priceType.$value['event_detail'][0]['event_timing'][0]['price'][0]['price'],
                    'image'=>$this->getEventImage($value['event_gallery']),
                );
            }

        
            // Get All Destination List
            $destinationList = array();
            $destinationList = Destination::with('DestinationGallery')->where('status','=',1)->limit(7)->get()->toArray();
            //set All the Image Path 
              foreach($destinationList as $key=>$value){
                    if(!empty($value['destination_gallery'])){
                        foreach($value['destination_gallery'] as $k=>$v){
                            $url = env('APP_URL').'/storage/app/public/destination/'.$v['image'];
                             $destinationList[$key]['destination_gallery'][$k]=$url;
                        }     
                    }       
                }

            
            $settingArr = BannerGallery::where('status','=',1)
                        ->where('banner_type_id','=',1)
                        ->where('is_default','=',1)
                        ->get()
                        ->toArray();
            //set All the Image Path 
            foreach($settingArr as $k=>$v){
                $url = env('APP_URL').'/storage/app/public/banner/'.$v['image'];
                $settingArr[$k]=$url;
            }
            $responseArray['status'] = true;
            $responseArray['code'] = 200;
            $responseArray['data'] =$settingArr;
            $responseArray['setting'] =$globalArr;
            $responseArray['destinationList'] =$destinationList;
            $responseArray['eventFinalArr'] =$eventFinalArr;
        }catch (Exception $e) {
            $responseArray['status'] = false;
            $responseArray['code'] = 500;
            $responseArray['message'] = $e->getMessage();
        }
        return response()->json($responseArray);

    }



    
    public function getAllEventList(Request $request){
        try{
            $all = $request->all();
            $page_no = $all['page_no'];
            $globalArr = Setting::all();

            $priceType = $globalArr['14']['options_value'];
            $eventItem = array();
            $events = Event::with('EventDetail','EventGallery')->where('status','=',1)->orderBy('id', 'ASC')->paginate(400);
            $event = $events->toArray();
            

            // echo "<pre>";
            // print_r($event);
            // die;
            $allEventList = array();
            foreach($event['data'] as $item){
                if(!empty($item['event_detail'])){
                    if(!empty($item['event_detail'][0]['event_timing'])){
                        foreach($item['event_detail'][0]['event_timing'] as $k=>$v){
                            if($item['event_detail'][0]['event_timing'][$k]['status']==1){
                                unset($item['event_detail'][0]['event_timing'][$k]['theatre']['event_seat']); 
                                $allEventList[]  = array(
                                    'event_id'=>$item['id'],
                                    'event_details_id'=>$item['event_detail'][0]['id'],
                                    'id'=>$item['event_detail'][0]['event_timing'][$k]['id'],
                                    'start_time'=>$item['event_detail'][0]['event_timing'][$k]['event_start_time'],
                                    'end_time'=>$item['event_detail'][0]['event_timing'][$k]['event_end_time'],
                                    'title'=>$item['event_detail'][0]['event']['title'],
                                    'place'=>$this->getCityNameById($item['event_detail'][0]['event_timing'][$k]['theatre']['city_id']),
                                    'price'=>$priceType.$item['event_detail'][0]['event_timing'][$k]['price'][0]['price'],
                                    'image'=>$this->getEventFeatureImage($item['event_gallery']),
                                ); 
                            }  
                        }
                        $eventItem[]=$item;
                    }
                }
            }
            
            $eventFinalArr = array();
            $chunkArray = array_chunk($allEventList,4);
            if($page_no>count($chunkArray)){
                $responseArray['status'] = false;
                $responseArray['code'] = 500;
                $responseArray['message'] = "No More Events";
            }else{
                $eventList = $chunkArray[0];
                if($page_no > 0){
                    $eventList = $chunkArray[$page_no];
                }

                //Set the Image Size
                foreach($eventList as $key=>$item){
                    if($key==0 || $key==3){ 
                        $eventList[$key]['image'] = str_replace('683X739','683X349',$item['image']);
                    }
                }
                $responseArray['status'] = true;
                $responseArray['code'] = 200;
                $responseArray['eventFinalArr'] =$eventList;
                //unset($event['data']);
                //$responseArray['eventPaginationData'] =$event;
                $responseArray['setting'] =$globalArr;
                if(($page_no+1)<count($chunkArray)){
                    $responseArray['next_page'] =$page_no+1;
                }else{
                    $responseArray['next_page'] ='-1';
                }
                $responseArray['total'] =count($allEventList);
            }
        }catch (Exception $e) {
            $responseArray['status'] = false;
            $responseArray['code'] = 500;
            $responseArray['message'] = $e->getMessage();
        }
        return response()->json($responseArray);

    }

    

    





}
