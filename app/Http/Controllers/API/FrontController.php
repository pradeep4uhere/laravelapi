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

class FrontController extends MasterController
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
                $eventFinalArr[]=array(
                    'event_id'=>$value['id'],
                    'id'=>$value['event_detail'][0]['event_timing'][0]['id'],
                    'title'=>$value['event_detail'][0]['event_timing'][0]['theatre']['theater_name'],
                    'place'=>$value['event_detail'][0]['city']['city_name'],
                    'price'=>$priceType.$value['event_detail'][0]['event_timing'][0]['price'][0]['price'],
                    'image'=>$this->getEventImage($value['event_gallery']),
                );
            }
            $responseArray['status'] = 'success';
            $responseArray['event'] = $eventFinalArr;
            $responseArray['code'] = '200';

        }else{
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['message'] = 'Invalid Requets';

        }
        return response()->json($responseArray, $this->successStatus); 
    }




    public function getSettingList(Request $request){
        try{
            $settingArr = Setting::all();

            //Get all Review List
            $reviewVideos = ReviewVideo::where('status','=',1)->get();
            $responseArray['status'] = true;
            $responseArray['code'] = 200;
            $responseArray['data'] =array('setting'=>$settingArr,'review'=>$reviewVideos);
            
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
            $eventDetails = Event::with('EventDetail','EventGallery')->where('id','=',$id)->orderBy('id', 'desc')->get()->toArray();

            //Select Event Timing What User Clicked On Event e.g Timing ID 
            if(!empty($eventDetails)){
                $gallery = $eventDetails[0]['event_gallery'];
                //storage/app/public/event/4003590a7e9a52292ca2526a74a58a11.jpeg
                if(!empty($eventDetails[0]['event_gallery'])){
                    foreach($eventDetails[0]['event_gallery'] as $imageItem){
                        $url = env('APP_URL').'/storage/app/public/event/'.$imageItem['image_thumb'];
                        $durl = env('APP_URL').'/storage/app/public/event/'.$imageItem['image_thumb'];
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
                $banner = env('APP_URL').'/storage/app/public/event/'.$eventDetails[0]['banner'];
                $eventParent = array('id'=>$eid, 'title'=>$title, 'durration'=>$durration,'description'=>$description,'banner'=>$banner);
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
            if(array_key_exists('id', $all) && !empty($request->get('id'))){
                $id = $request->get('id'); 
                $settingArr = Destination::with('DestinationGallery')->where('status','=',1)->where('id','=',$id)->get()->toArray();
                //print_r($settingArr[0]['destination_gallery']);die;
                if(!empty($settingArr[0]['destination_gallery'])){
                        foreach($settingArr[0]['destination_gallery'] as $k=>$v){
                             $url = env('APP_URL').'/storage/app/public/destination/'.$v['image'];
                             $settingArr[0]['destination_gallery'][$k]=array('original'=>$url,'thumbnail'=>$url);
                        }     
                    }
            }else{
                $settingArr = Destination::with('DestinationGallery')->where('status','=',1)->get()->toArray();
                //set All the Image Path 
                foreach($settingArr as $key=>$value){
                    if(!empty($value['destination_gallery'])){
                        foreach($value['destination_gallery'] as $k=>$v){
                            $url = env('APP_URL').'/storage/app/public/destination/'.$v['image'];
                             $settingArr[$key]['destination_gallery'][$k]=$url;
                        }     
                    }       
                }
            }
            
            $responseArray['status'] = true;
            $responseArray['code'] = 200;
            $responseArray['data'] =$settingArr;
            
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
            $globalArr = Setting::all();

            $priceType = $globalArr['14']['options_value'];
            $eventItem = array();
            $events = Event::with('EventDetail','EventGallery')->where('status','=',1)->orderBy('id', 'desc')->paginate(6);
            $event = $events->toArray();
           
            foreach($event['data'] as $item){
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
                    'event_details_id'=>$value['event_detail'][0]['id'],
                    'id'=>$value['event_detail'][0]['event_timing'][0]['id'],
                    'title'=>$value['event_detail'][0]['event_timing'][0]['theatre']['theater_name'],
                    'place'=>$value['event_detail'][0]['city']['city_name'],
                    'price'=>$priceType.$value['event_detail'][0]['event_timing'][0]['price'][0]['price'],
                    'image'=>$this->getEventImage($value['event_gallery']),
                );
            }
            $responseArray['status'] = true;
            $responseArray['code'] = 200;
            $responseArray['eventFinalArr'] =$eventFinalArr;
            unset($event['data']);
            $responseArray['eventPaginationData'] =$event;
            
            
        }catch (Exception $e) {
            $responseArray['status'] = false;
            $responseArray['code'] = 500;
            $responseArray['message'] = $e->getMessage();
        }
        return response()->json($responseArray);

    }

    

    





}
