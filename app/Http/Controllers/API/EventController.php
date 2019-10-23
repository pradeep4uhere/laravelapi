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
use App\TempSeatBooking;


class EventController extends MasterController
{
    public $successStatus = 200;

    public function getEventList(Request $request){
        $responseArray = array();
        $eventList = Event::paginate(10);
        $links = $eventList->links();
        $responseArray['status'] = 'success';
        $responseArray['code'] = '200';
        $responseArray['event'] = $eventList;
        return response()->json(['data' => $responseArray], $this->successStatus); 
    }

    public function addEvent(Request $request){
        $responseArray = array();
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:events|max:255',
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
            $event = new Event();
            $event->title = trim($data['title']);
            $event->description = trim($data['description']);
            $event->durration = $data['durration'];
            $event->status = $data['status'];
            $event->created_at = self::getCreatedDate();
            if($event->save()){
                $responseArray['status'] = true;
                $responseArray['code']= "200";
                $responseArray['message']= "Event Added Successfully!!";
                $responseArray['latest_id']= $event->id;
            }else{
                $responseArray['status'] = false;
                $responseArray['code']= "500";
                $responseArray['message']= "Opps! Somthing went wrong";
            }
        }
        return response()->json(['data' => $responseArray], $this->successStatus); 

    }




    public function getEventDetails(Request $request){
        $responseArray = array();
        $id = $request->get('id');
        $event = Event::with('EventDetail','EventGallery')->find($id);

        $imgGalleryList = array();
        $imgGalleryList[] = array(
            "src"=>env('APP_URL').'/storage/app/public/event/'.$event['banner'],
            "thumbnail"=>env('APP_URL').'/storage/app/public/event/'.$event['banner'],
            "thumbnailWidth"=>375,
            "thumbnailHeight"=>250,
            "caption"=>""
        );

        if(!empty($event)){
            $responseArray['status'] = true;
            $responseArray['code']= "200";
            $responseArray['data']= $event;
            $responseArray['imagesList'] = $imgGalleryList;
        }else{
            $responseArray['status'] = false;
            $responseArray['code']= "500";
            $responseArray['message']= "Opps! Somthing went wrong, No Event Found";
        }
        return response()->json([$responseArray]); 

    }



    public function getEventbanner(Request $request){
        echo "dasdsa"; die;

    }



    public function saveEventBanner(Request $request){
        echo "dasdsad"; die;
    }



    public function saveEventDetails(Request $request){
        $responseArray = array();
        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'language'=> 'required',
            'country'  => 'required',
            'state'  => 'required',
            'city'  => 'required',
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
            $id = $request->get('event_id');
            $eventDetails = \App\EventDetail::where('event_id','=',$id)->get();
            if($eventDetails->count()){
                $eventDetails = $eventDetails->toArray();
                $eventDetailsId = $eventDetails[0]['id'];
                $eventDObj = EventDetail::find($eventDetailsId);
                $eventDObj->language_id    = $request->get('language');
                $eventDObj->country_id     = $request->get('country');
                $eventDObj->state_id       = $request->get('state');
                $eventDObj->city_id        = $request->get('city');
                if($eventDObj->save()){
                    $responseArray['status'] = true;
                    $responseArray['code']= "200";
                    $responseArray['message']= "Event Details updated successfully";
                    $responseArray['data']= $eventDetails;
                }
            }else{
                $eventDObj = new EventDetail();
                $eventDObj->event_id = $request->get('event_id');
                $eventDObj->language_id = $request->get('language');
                $eventDObj->country_id = $request->get('country');
                $eventDObj->state_id = $request->get('state');
                $eventDObj->city_id = $request->get('city');
                $eventDObj->status = 1;
                $eventDObj->created_at = self::getCreatedDate();
                if($eventDObj->save()){
                    $responseArray['status'] = true;
                    $responseArray['code']= "200";
                    $responseArray['message']= "Event Details updated successfully.";
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
    



    public function deleteEventTiming(Request $request){
        $id = $request->get('id');
        $eventtiming = $eventDetails = EventTiming::find($id);
        if(!empty($eventtiming)){
            $this->deleteAllPriceOfEventTiming($eventtiming);
            if($eventtiming->delete()){
                $responseArray['status'] = 'success';
                $responseArray['code']= "200";
                $responseArray['message']= "Event timing deleted."; 
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
            "src"=>env('APP_URL').'/storage/app/public/event/'.$imageList['banner'],
            "thumbnail"=>env('APP_URL').'/storage/app/public/event/'.$imageList['banner'],
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
                Storage::disk('event')->put($file, base64_decode($data));
                if($this->saveEventImage($id,$file)){
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
        $imageList = EventGallery::where('event_id','=',$id)->orderBy('id','DESC')->get();
        $imgGalleryList = array();
        foreach($imageList as $item){
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
       $responseArray['status'] = true;
       $responseArray['code']= "200";
       $responseArray['imagesList'] = $imgGalleryList;
       return response()->json(['data' => $responseArray], $this->successStatus); 
    }

    private function saveEventBannerImage($id,$imageName){
        $eventGallery = Event::find($id);
        $eventGallery->banner = $imageName;
        if($eventGallery->save()){
            return true;
        }else{
            return false;
        }

    }


    private function saveEventImage($id,$imageName){
        $eventGallery = new EventGallery();
        $eventGallery['event_id'] = $id;
        $eventGallery['image'] = $imageName;
        $eventGallery['image_thumb'] = $imageName;
        $eventGallery['media_type'] = 'image';
        $eventGallery['status'] = '1';
        $eventGallery['created_at'] = self::getCreatedDate();
        if($eventGallery->save()){
            return true;
        }else{
            return false;
        }
    }




  public function deleteEventImage(Request $request){
         $id = $request->get('id');
         $eventImage = EventGallery::find($id);
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



    public function defaultEventImage(Request $request){
    
         $id = $request->get('id');
         $eventImage = EventGallery::find($id);
         DB::table('event_galleries')->where(['event_id'=>$id])->update(['is_default' => 0]);
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





    
    public function deleteEvent(Request $request){
    
         $id = $request->get('id');
         $eventTimingIds = array();
         $event = Event::with('EventDetail','EventGallery')->find($id)->toArray();
         if(!empty($event)){
            //Check If this event has details      
            if(!empty($event['event_detail'])){
                //Check if this event has timting     
                if(!empty($event['event_detail'][0]['event_timing'])){
                    //Get all the Event Timing Ids
                    foreach($event['event_detail'][0]['event_timing'] as $item){
                        //check if any event timing has booking or not
                        $eventTimingIds[]=$item['id'];                    
                    }
                }       
            } 
         }
        
         if(!empty($eventTimingIds)){
            //Check If Event Timing has any Booking 
            $eventTiming = TempSeatBooking::whereIn('event_timing_id', array($eventTimingIds))->get();
         }else{
            $eventTiming = array();
         }
         if(!empty($eventTiming)){
             $responseArray['status'] = false;
             $responseArray['code']= "500";
             $responseArray['message']= "Event has booking, can not be delete!!";
         }else{
            $event = Event::find($id);
            $event_id = $event['id'];
            //Delete All Gallery
            $eventImage = EventGallery::where('event_id','=',$event_id)->delete(); 

            //Get event Details
            $evetnDetails  = EventDetail::where('event_id','=',$event_id)->get();
            if($evetnDetails->count()>0){
                foreach($evetnDetails as $edItem){
                    EventTiming::where("event_detail_id",'=',$edItem['id'])->delete();                
                }   
                EventDetail::where('event_id','=',$event_id)->delete();  
                
            }
            Event::where('id','=',$id)->delete();
            $responseArray['status'] = true;
            $responseArray['code']= "200";
            $responseArray['message']= "Event deleted with all related data!!";
         }
         return response()->json(['data' => $responseArray], $this->successStatus); 
    
    }





















}
