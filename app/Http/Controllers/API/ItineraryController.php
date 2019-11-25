<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MasterController;
use Auth;
use App\User;
use App\Setting;
use App\Event;
use Session;
use App\EventDetail;
use App\EventTiming;
use Storage;
use App\EventGallery;
use App\Price;
use DB;
use App\Itinerary;
use App\ItineraryGallery;
use App\ItineraryDeparture;
use App\ItineraryDay;
use App\ItineraryDayGallery;
use App\ItineraryAddon;

class ItineraryController extends MasterController
{
    public $successStatus = 200;

    //Upload Day image of the Itinerary
    public function dayImageUpload(Request $request){
        
        $id= $request->get('id');
        $responseArray = array();
        if($request->get('imageStr')){
         foreach($request->get('imageStr') as $file){
                 list($type, $data) = explode(';', $file);
                 list(, $data)      = explode(',', $file);
                 $datas = base64_decode($data);
                 $typeArr = explode('/', $type);
                 $file = md5(uniqid()) . '.'.end($typeArr);
                 Storage::disk('itineraryday')->put($file, base64_decode($data));
                 if($this->saveItineraryDayGalleryImage($id,$file)){
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
         $itineraryDay = ItineraryDay::find($id);
         $imageList = ItineraryDayGallery::where('itinerary_day_id','=',$id)->orderBy('id','DESC')->get();
         $imgGalleryList = array();
         foreach($imageList as $item){
             $imgGalleryList[] = array(
                 "src"=>env('APP_URL').'/storage/app/public/itineraryday/'.$item['image'],
                 "thumbnail"=>env('APP_URL').'/storage/app/public/itineraryday/'.$item['image'],
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
        $responseArray['itineraryDay'] = $itineraryDay;
        return response()->json(['data' => $responseArray], $this->successStatus); 
     }
 


    private function saveItineraryDayGalleryImage($id,$imageName){
        $eventGallery = new ItineraryDayGallery();
        $eventGallery['itinerary_day_id'] = $id;
        $eventGallery['image'] = $imageName;
        $eventGallery['image_thumb'] = $imageName;
        $eventGallery['media_type'] = 'image';
        $eventGallery['status'] = '1';
        $eventGallery['created_at'] = self::getCreatedDate();
        if($eventGallery->save()){
            $lastId= $eventGallery->id;
            $this->resizeDayImage($lastId);
            return true;
        }else{
            return false;
        }
    }

    private function resizeDayImage($id){
        $imageArr = ItineraryDayGallery::find($id);
        $imageUrl = env('APP_URL').'/storage/app/public/itineraryday/'.$imageArr['image'];
        $resize = \Image::make($imageUrl);
                
        // resize image to fixed size 1139X627
        $resize->resize(658,494)->save($imageArr['image']);
        Storage::disk('itineraryday/resize/658X494')->put($imageArr['image'], $resize);

        // resize image to fixed size 372X253
        $resize->resize(128,96)->save($imageArr['image']);
        Storage::disk('itineraryday/resize/128X96')->put($imageArr['image'], $resize);

        // resize image to fixed size 75X68
        $resize->resize(75,68)->save($imageArr['image']);
        Storage::disk('itineraryday/resize/75X68')->put($imageArr['image'], $resize);


    }



    public function addItinerary(Request $request){
        $responseArray = array();
        $data = $request->all();
        if($request->get('id')){
            $validator = Validator::make($request->all(), [
                'title' => 'required|unique:itineraries,id|max:255',
                'description'=> 'required',
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'title' => 'required|unique:itineraries|max:255',
                'description'=> 'required',
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
            return response()->json(['data' => $responseArray], $this->successStatus); 
            die;
        }else{
            $data = $request->all();
            if($request->get('id')){
                $id = $data['id'];
            	$event = Itinerary::find($id);
            	$event->title = trim($data['title']);
	            $event->description = trim($data['description']);
	            $event->trip_type = $data['addon'];
	            $event->status = $data['status'];

            }else{
	            $event = new Itinerary();
	            $event->title = trim($data['title']);
	            $event->description = trim($data['description']);
	            $event->trip_type = $data['addon'];
	            $event->status = $data['status'];
	            $event->created_at = self::getCreatedDate();
	        }
            if($event->save()){
                $responseArray['status'] = true;
                $responseArray['code']= "200";
                $responseArray['message']= "Itinerary Updated Successfully!!";
                $responseArray['latest_id']= $event->id;
            }else{
                $responseArray['status'] = false;
                $responseArray['code']= "500";
                $responseArray['message']= "Opps! Somthing went wrong";
            }
        	
        }
        return response()->json(['data' => $responseArray], $this->successStatus); 

    }

    
    public function updateItineraryDays(Request $request){
        $responseArray = array();
        $validator = Validator::make($request->all(), [
            'day' => 'required|unique:itinerary_days,itinerary_id|max:255',
            'details'=> 'required',
            'place_name'=>'required'
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
            $data = $request->all();
            if(array_key_exists('id',$data)){
            	$id = $data['id'];
            	$event = ItineraryDay::find($id);
	            $event->day = trim($data['day']);
                $event->place_name = $data['place_name'];
                $event->details =   $data['details'];
	            $event->status = $data['status'];

            }
            if($event->save()){
                $responseArray['status'] = true;
                $responseArray['code']= "200";
                $responseArray['message']= "Itinerary Days updated Successfully!!";
                $responseArray['latest_id']= $event->id;
            }else{
                $responseArray['status'] = false;
                $responseArray['code']= "500";
                $responseArray['message']= "Opps! Somthing went wrong";
            }
        	
        }
        return response()->json(['data' => $responseArray], $this->successStatus); 

    }
    
    public function addItineraryDays(Request $request){
        $responseArray = array();
        $validator = Validator::make($request->all(), [
            'day' => 'required|unique:itinerary_days,itinerary_id|max:255',
            'details'=> 'required',
            'place_name'=>'required'
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
            $data = $request->all();
            if(array_key_exists('id',$data)){
            	$id = $data['id'];
            	$event = ItineraryDay::find($id);
            	$event->itinerary_id = trim($data['itinerary_id']);
	            $event->day = trim($data['day']);
                $event->place_name = $data['place_name'];
                $event->details =   $data['details'];
	            $event->status = $data['status'];

            }else{
	            $event = new ItineraryDay();
	            $event->itinerary_id = trim($data['itinerary_id']);
	            $event->day          = trim($data['day']);
                $event->place_name   = $data['place_name'];
                $event->details     =   $data['details'];
	            $event->status = $data['status'];
	            $event->created_at = self::getCreatedDate();
	        }
            if($event->save()){
                $responseArray['status'] = true;
                $responseArray['code']= "200";
                $responseArray['message']= "Itinerary Days added Successfully!!";
                $responseArray['latest_id']= $event->id;
            }else{
                $responseArray['status'] = false;
                $responseArray['code']= "500";
                $responseArray['message']= "Opps! Somthing went wrong";
            }
        	
        }
        return response()->json(['data' => $responseArray], $this->successStatus); 

    }



     public function allItinerary(Request $request){
        $responseArray = array();
        $eventList = Itinerary::with('ItineraryGallery')->paginate(20000);
        $links = $eventList->links();
        /****************Datatable Start***************/
        $columns = array(
            array('label'=>'SN','field'=>'id','sort'=>'asc','width'=>'25'),
            array('label'=>'Itinirary Name','field'=>'title','sort'=>'asc','width'=>'170'),
            array('label'=>'Description','field'=>'description','sort'=>'asc','width'=>'100'),
            array('label'=>'AddOn','field'=>'addon','sort'=>'asc','width'=>'100'),
            array('label'=>'Image','field'=>'itinerary_gallery','sort'=>'asc','width'=>'100'),
            array('label'=>'Trip Type','field'=>'trip_type','sort'=>'asc','width'=>'100'),
            array('label'=>'Status','field'=>'status','sort'=>'asc','width'=>'100'),
            array('label'=>'Created On','field'=>'created_at','sort'=>'asc','width'=>'100'),
            array('label'=>'Action','field'=>'action','sort'=>'asc','width'=>'100')
            );
        $row = [];
        $actionStr ='';
        foreach($eventList as $item){
            $row[] =array(
                'id'=>$item['id'],
                'title'=>$item['title'],
                'description'=>substr(strip_tags($item['description']),0,20),
                'addon'=>substr(strip_tags($item['addon']),0,20),
                'itinerary_gallery'=>count($item['ItineraryGallery']),
                'trip_type'=>($item['trip_type']!='')?$item['trip_type']:"--",
                'status'=>$item['status'],
                'created_at'=>date("d-M-Y",strtotime($item['created_at']->toDateTimeString())),
                'action'=>$actionStr
            );
        }
        $dataTable = array();
        $dataTable['columns'] =$columns;
        $dataTable['rows'] =$row;
        /****************Datatable Ends now***************/

        $responseArray['status'] = 'success';
        $responseArray['code'] = '200';
        $responseArray['event'] = $eventList;
        $responseArray['dataTable'] = $dataTable;


        return response()->json(['data' => $responseArray], $this->successStatus); 
    }


    public function itineraryDaysList(Request $request){
        $responseArray = array();
        $id = $request->get('id');
        $eventList = ItineraryDay::with('Itinerary','ItineraryDayGallery')->where('itinerary_id','=',$id)->paginate(10000);
        $links = $eventList->links();
        $responseArray['status'] = 'success';
        $responseArray['code'] = '200';
        $responseArray['event'] = $eventList;
        return response()->json(['data' => $responseArray], $this->successStatus); 
    }


    



    

    public function getItineraryDay(Request $request){
		$setting = Setting::all();
        $responseArray = array();
        $id = $request->get('id');
        $ItineraryDay = ItineraryDay::with('Itinerary')->find($id);
        if(!empty($ItineraryDay)){
            $responseArray['status'] = true;
            $responseArray['code']= "200";
            $responseArray['itinerary_day']= $ItineraryDay;

        }else{
            $responseArray['status'] = false;
            $responseArray['code']= "500";
            $responseArray['message']= "Opps! Somthing went wrong, No Event Found";
        }
        return response()->json([$responseArray]); 

    }

    public function getitinerary(Request $request){
		$setting = Setting::all();
        $responseArray = array();
        $id = $request->get('id');
        $event = Itinerary::with('ItineraryDeparture','ItineraryAddon','ItineraryTermsAndConditions')->find($id);
        if(!empty($event)){
            $responseArray['status'] = true;
            $responseArray['code']= "200";
            $responseArray['data']= $event;
            $responseArray['data']['setting']= $setting;

        }else{
            $responseArray['status'] = false;
            $responseArray['code']= "500";
            $responseArray['message']= "Opps! Somthing went wrong, No Event Found";
        }
        return response()->json([$responseArray]); 

    }



    public function itineraryDepartureUpdate(Request $request){
        $responseArray = array();
        $validator = Validator::make($request->all(), [
            'price' => 'required',
            'start_date'=> 'required',
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
 			
            $id = $request->get('id');
            if($id!=''){
            	$eventDetails = \App\ItineraryDeparture::find($id);
	            if($eventDetails->count()){
	                $eventDObj = ItineraryDeparture::find($id);
	                $eventDObj->itinerary_id    = $request->get('itinerary_id');
	                $eventDObj->start_date      = $request->get('start_date');
	                $eventDObj->end_date        = $request->get('end_date');
	                $eventDObj->price           = $request->get('price');
	                $eventDObj->status          = $request->get('status');
	                if($eventDObj->save()){
	                    $responseArray['status'] = true;
	                    $responseArray['code']= "200";
	                    $responseArray['message']= "Itinerary Departure Details updated successfully";
	                    $responseArray['data']= $eventDetails;
	                }
	            }
        	}else{
        	    $eventDObj = new \App\ItineraryDeparture();
                $eventDObj->itinerary_id 	= $request->get('itinerary_id');
                $eventDObj->start_date 		= $request->get('start_date');
                $eventDObj->end_date 		= $request->get('end_date');
                $eventDObj->price 			= $request->get('price');
                $eventDObj->status 			= $request->get('status');
                $eventDObj->status 			= 1;
                $eventDObj->created_at 		= self::getCreatedDate();
                if($eventDObj->save()){
                    $responseArray['status'] = true;
                    $responseArray['code']= "200";
                    $responseArray['message']= "Itinerary Departure Details updated successfully.";
                }else{
                    $responseArray['status'] = 'error';
                    $responseArray['code']= "500";
                    $responseArray['message']= "Somthing went wrong, please try agian later.";
                }
            }
        }
        return response()->json([$responseArray]);
    }



    public function getEventLocation(Request $request){
        $id = $request->get('event_id');
        if($id>0){
            $eventDetails = \App\EventDetail::where('event_id','=',$id)->get();   
            $responseArray['status'] = true;
            $responseArray['code']= "200";
            $responseArray['data']= $eventDetails;   
        }else{
            $responseArray['status'] = false;
            $responseArray['code']= "500";
            $responseArray['message']= "No records found for event.";
        }
        return response()->json([$responseArray]);
    }



    public function updateEventTiming(Request $request){
        $body = $request->get('body');
        $id = $request->get('id');
        if($id>0 || $id!=''){
            $eventDetails = EventTiming::find($id);
        }else{
            $eventDetails = new EventTiming();
        }
        $event_detail_id = $request->get('event_id');
        $theater_id = $request->get('theater_id');
        $eventDetails['event_detail_id']    = $request->get('event_id');
        $eventDetails['theatre_id']         = $request->get('theater_id');
        $eventDetails['event_start_time']   = $request->get('event_start_time');
        $eventDetails['event_end_time']     = $request->get('event_end_time');
        $eventDetails['status']             = $request->get('status');

        $eventDetails['itinerary']          = $body['itinerary'];
        $eventDetails['includes']           = $body['includes'];
        $eventDetails['dincludes']          = $body['dincludes'];
        $eventDetails['other']              = $body['other'];

        
        $eventDetails['created_at']         = self::getCreatedDate();
        //echo "<pre>";
        //print_r($eventDetails);die;
      
        if($eventDetails->save()){

            if($this->saveEventPrice($request,$eventDetails)){
                $responseArray['status'] = 'success';
                $responseArray['code']= "200";
                $responseArray['message']= "Event timing updated."; 
            }else{
                $responseArray['status'] = 'success';
                $responseArray['code']= "200";
                $responseArray['message']= "Event timing updated, Price is not updated"; 
            }
            
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code']= "500";
            $responseArray['message']= "Somthing went wrong, Please try after sometime.";
        }
        return response()->json([$responseArray]);

    }


    
    public function updateItineraryAddon(Request $request){
        $body = $request->get('body');
        $id = $request->get('id');
        if($id>0 || $id!=''){
            $eventDetails = ItineraryAddon::find($id);
        }else{
            $eventDetails = new ItineraryAddon();
        }
        $itinerary_id = $request->get('itinerary_id');
        $eventDetails['itinerary_id']   = $itinerary_id;
        $eventDetails['title']          = $request->get('title');
        $eventDetails['descriptions']   = $request->get('description');
        $eventDetails['addon_description']   = $request->get('addon_descriptions');
        $eventDetails['addon_includes']   = $request->get('addon_includes');
        $type = $request->get('type');
        if($type==1){
            $eventDetails['type']   = 1;
        }
        if($type==2){
            $eventDetails['type']   = 2;
        }
        
        
        $eventDetails['status']         = $request->get('status');
        $eventDetails['created_at']     = self::getCreatedDate();
      
        if($eventDetails->save()){
            $responseArray['status'] = 'success';
            $responseArray['code']= "200";
            $responseArray['message']= "itinerary addon updated."; 
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code']= "500";
            $responseArray['message']= "Somthing went wrong, Please try after sometime.";
        }
        return response()->json([$responseArray]);

    }


    /*
     *@Author       :: Pradeep Kumar
     *@Description  :: Update Price of the Event Posted while adding Event Timing
     *@Created Date :: 13 June 2019
     */
    private function saveEventPrice($request,$eventDetails){
        $body           = $request->get('body');
        $priceArr       = $body['price'];
        $sitingTypeArr  = $body['sitting_type_id'];
        $sitingPrice    = array_combine($sitingTypeArr,$priceArr);
        $event_timing_id= $eventDetails->id;
        $this->deleteAllPriceOfEventTiming($eventDetails);
        $count = 0;
        if(!empty($sitingPrice)){
            foreach($sitingPrice as $k=>$v){
                if($v!=''){
                    $priceObj = new Price();
                    $priceObj->event_timing_id = $event_timing_id;
                    $priceObj->sitting_type_id = $k;
                    $priceObj->price           = $v;
                    $priceObj->save();
                    $count++;
                }
            }
        }
        if(count($sitingPrice)==$count){
            return true;
        }else{
            return false;
        }
    }


     /*
     *@Author       :: Pradeep Kumar
     *@Description  :: Delete All Event Timing Price
     *@Created Date :: 13 June 2019
     */
    private function deleteAllPriceOfEventTiming($eventtiming){
        $event_timing_id= $eventtiming->id;
        $res = DB::table('prices')->where('event_timing_id', '=', $event_timing_id)->delete();
        if($res){
            return true;
        }else{
            return false;
        }
    }
    



    public function itineraryDepartureDelete(Request $request){
        $id = $request->get('id');
        $eventtiming = $eventDetails = ItineraryDeparture::find($id);
        if(!empty($eventtiming)){
            if($eventtiming->delete()){
                $responseArray['status'] = 'success';
                $responseArray['code']= "200";
                $responseArray['message']= "Itinerary Departure Deleted."; 
            }else{
                $responseArray['status'] = 'errro';
                $responseArray['code']= "500";
                $responseArray['message']= "somthing went wrong, plz try after sometime"; 
            }
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code']= "500";
            $responseArray['message']= "Invalid request, No Event Timing found.";
        }
        return response()->json([$responseArray]);
    }



    public function deleteItineraryDays(Request $request){
        $id = $request->get('id');
        $eventtiming = $eventDetails = ItineraryDay::find($id);
        if(!empty($eventtiming)){
            //$eventtiming->delete()
            if($eventtiming->delete()){
                $responseArray['status'] = 'success';
                $responseArray['code']= 200;
                $responseArray['message']= "Itinerary Day Deleted."; 
            }else{
                $responseArray['status'] = 'errro';
                $responseArray['code']= 500;
                $responseArray['message']= "somthing went wrong, plz try after sometime"; 
            }
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code']= 500;
            $responseArray['message']= "Invalid request, No Event Timing found.";
        }
        return response()->json([$responseArray]);
    }



    public function itineraryAddonDelete(Request $request){
        $id = $request->get('id');
        $eventtiming = $eventDetails = ItineraryAddon::find($id);
        if(!empty($eventtiming)){
            //$eventtiming->delete()
            if($eventtiming->delete()){
                $responseArray['status'] = 'success';
                $responseArray['code']= 200;
                $responseArray['message']= "Itinerary Addon Deleted."; 
            }else{
                $responseArray['status'] = 'errro';
                $responseArray['code']= 500;
                $responseArray['message']= "somthing went wrong, plz try after sometime"; 
            }
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code']= 500;
            $responseArray['message']= "Invalid request, No Event Timing found.";
        }
        return response()->json([$responseArray]);
    }


    


    
      public function itineraryDelete(Request $request){
        $id = $request->get('id');
        $eventtiming = $eventDetails = Itinerary::with('ItineraryDeparture')->find($id);
        if(!empty($eventtiming)){
        	//Delete All itineraryDepartureDelete
        	if(!empty($eventtiming['ItineraryDeparture']->count())){
        		$count=1;
        		foreach($eventtiming['ItineraryDeparture'] as $item){
        			$itineraryDeparture = ItineraryDeparture::find($item['id']);
        			$itineraryDeparture->delete();
        			$count++;
        		}
        	}
            if($eventtiming->delete()){
                $responseArray['status'] = 'success';
                $responseArray['code']= "200";
                $responseArray['message']= "Itinerary  Deleted."; 
            }else{
                $responseArray['status'] = 'errro';
                $responseArray['code']= "500";
                $responseArray['message']= "somthing went wrong, plz try after sometime"; 
            }
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code']= "500";
            $responseArray['message']= "Invalid request, No Event Timing found.";
        }
        return response()->json($responseArray);
    }



    public function updateEvent(Request $request){
        $responseArray = array();
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'description'=> 'required',
            'durration'  => 'required',
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
            $data = $request->all();
            $event = Event::find($data['id']);
            $event->title = trim($data['title']);
            $event->description = trim($data['description']);
            $event->durration = $data['durration'];
            $event->status = $data['status'];
            $event->is_feature=$data['is_feature'];
            $event->created_at = self::getCreatedDate();
            //print_r($event);die;            
            //dd($event);
            if($event->save()){
                $responseArray['status'] = true;
                $responseArray['code']= "200";
                $responseArray['message']= "Event updated Successfully!!";
            }else{
                $responseArray['status'] = false;
                $responseArray['code']= "500";
                $responseArray['message']= "Opps! Somthing went wrong";
            }
        }
        return response()->json(['data' => $responseArray], $this->successStatus); 

    }





    //Save Event 
    public function eventBannerUpload(Request $request){
        $id= $request->get('id');
       $responseArray = array();
       if($request->get('imageStr')){
        foreach($request->get('imageStr') as $file){
                list($type, $data) = explode(';', $file);
                list(, $data)      = explode(',', $file);
                $datas = base64_decode($data);
                $typeArr = explode('/', $type);
                $file = md5(uniqid()) . '.'.end($typeArr);
                Storage::disk('event')->put($file, base64_decode($data));
                if($this->saveEventBannerImage($id,$file)){
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
        $imageList = Event::find($id);
        $imgGalleryList = array();
        $imgGalleryList[] = array(
            "src"=>env('APP_URL').'/storage/app/public/itinerary/'.$imageList['banner'],
            "thumbnail"=>env('APP_URL').'/storage/app/public/itinerary/'.$imageList['banner'],
            "thumbnailWidth"=>375,
            "thumbnailHeight"=>250,
            "caption"=>""
        );
       $responseArray['status'] = true;
       $responseArray['code']= "200";
       $responseArray['imagesList'] = $imgGalleryList;
       return response()->json(['data' => $responseArray], $this->successStatus); 

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
                Storage::disk('itinerary')->put($file, base64_decode($data));
                if($this->saveEventImage($id,$file)){
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
        $imageList = ItineraryGallery::where('itinerary_id','=',$id)->orderBy('id','DESC')->get();
        $imgGalleryList = array();
        foreach($imageList as $item){
            $imgGalleryList[] = array(
                "src"=>env('APP_URL').'/storage/app/public/itinerary/'.$item['image'],
                "thumbnail"=>env('APP_URL').'/storage/app/public/itinerary/'.$item['image'],
                "thumbnailWidth"=>rand(250,375),
                "thumbnailHeight"=>rand(175,250),
                "caption"=>"",
                "id"=>$item['id'],
                "is_default"=>$item['is_default'],
                "status"=>$item['status'],
            );
        }
       $itineraryDetails = Itinerary::find($id);
       $responseArray['status'] = true;
       $responseArray['code']= "200";
       $responseArray['imagesList'] = $imgGalleryList;
       $responseArray['details'] = $itineraryDetails;
       
       return response()->json(['data' => $responseArray], $this->successStatus); 
    }

    private function saveEventBannerImage($id,$imageName){
        $eventGallery = Itinerary::find($id);
        $eventGallery->banner = $imageName;
        if($eventGallery->save()){
            return true;
        }else{
            return false;
        }

    }


    private function saveEventImage($id,$imageName){
        $eventGallery = new ItineraryGallery();
        $eventGallery['itinerary_id'] = $id;
        $eventGallery['image'] = $imageName;
        $eventGallery['image_thumb'] = $imageName;
        $eventGallery['media_type'] = 'image';
        $eventGallery['status'] = '1';
        $eventGallery['created_at'] = self::getCreatedDate();
        if($eventGallery->save()){
            $lastId= $eventGallery->id;
            $this->resizeImage($lastId);
            return true;
        }else{
            return false;
        }
    }



    private function resizeImage($id){
        $imageArr = ItineraryGallery::find($id);
        $imageUrl = env('APP_URL').'/storage/app/public/itinerary/'.$imageArr['image'];
        $resize = \Image::make($imageUrl);
                
        // resize image to fixed size 1139X627
        $resize->resize(1139,627)->save($imageArr['image']);
        Storage::disk('itinerary/resize/1139X627')->put($imageArr['image'], $resize);

        // resize image to fixed size 372X253
        $resize->resize(372,253)->save($imageArr['image']);
        Storage::disk('itinerary/resize/372X253')->put($imageArr['image'], $resize);

            // resize image to fixed size 75X68
            $resize->resize(75,68)->save($imageArr['image']);
            Storage::disk('itinerary/resize/75X68')->put($imageArr['image'], $resize);


    }





  public function deleteImage(Request $request){
         $id = $request->get('id');
         $eventImage = ItineraryGallery::find($id);
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



    public function defaultImage(Request $request){
    
         $id = $request->get('id');
         $eventImage = ItineraryGallery::find($id);
         DB::table('itinerary_galleries')->where(['itinerary_id'=>$eventImage->itinerary_id])->update(['is_default' =>0]);
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


   public function deletedayImage(Request $request){
    $id = $request->get('id');
    $eventImage = ItineraryDayGallery::find($id);
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


    


    public function defaultdayImage(Request $request){
    
        $id = $request->get('id');
        $eventImage = ItineraryDayGallery::find($id);
        DB::table('itinerary_day_galleries')->where(['itinerary_day_id'=>$eventImage->itinerary_day_id])->update(['is_default' =>0]);
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

    




    

    public function updateEventImageStatus(Request $request){
    
         $id = $request->get('id');
         $eventImage = EventGallery::find($id);
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
