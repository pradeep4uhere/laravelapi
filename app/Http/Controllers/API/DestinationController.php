<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MasterController;
use Auth;
use App\User;
use Session;
use DB;
use App\Destination;
use App\DestinationGallery;
use Storage;
    

class DestinationController extends MasterController
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


   


    public function allDestination(Request $request){
        if($request->isMethod('post'))
        {
            $destination = Destination::all();
            if($destination->count()){
                 /****************Datatable Start***************/
                    $columns = array(
                        array('label'=>'SN','field'=>'id','sort'=>'asc','width'=>'25'),
                        array('label'=>'Name','field'=>'title','sort'=>'asc','width'=>'170'),
                        array('label'=>'Description','field'=>'descriptions','sort'=>'asc','width'=>'100'),
                        array('label'=>'Altitude','field'=>'altitude','sort'=>'asc','width'=>'100'),
                        array('label'=>'Climate','field'=>'climate','sort'=>'asc','width'=>'100'),
                        array('label'=>'Population','field'=>'population','sort'=>'asc','width'=>'100'),
                        array('label'=>'Shopping','field'=>'shopping','sort'=>'asc','width'=>'100'),
                        array('label'=>'Cuisine','field'=>'cuisine','sort'=>'asc','width'=>'100'),
                        array('label'=>'Trip Type','field'=>'trip_type','sort'=>'asc','width'=>'100'),
                        array('label'=>'Status','field'=>'status','sort'=>'asc','width'=>'100'),
                        array('label'=>'Created On','field'=>'created_at','sort'=>'asc','width'=>'100'),
                        array('label'=>'Action','field'=>'action','sort'=>'asc','width'=>'100')
                        );
                    $row = [];
                    $actionStr ='';
                    foreach($destination as $item){
                        $row[] =array(
                            'id'=>$item['id'],
                            'title'=>($item['title']!='')?$item['title']:"--",
                            'descriptions'=>($item['descriptions']!='')?substr(strip_tags($item['descriptions']),0,20):"--",
                            'altitude'=>($item['altitude']!='')?$item['altitude']:"--",
                            'climate'=>($item['climate']!='')?$item['climate']:"--",
                            'population'=>($item['population']!='')?substr(strip_tags($item['population']),0,20):"--",
                            'shopping'=>($item['shopping']!='')?substr(strip_tags($item['shopping']),0,20):"--",
                            'cuisine'=>($item['cuisine']!='')?substr(strip_tags($item['cuisine']),0,20):"--",
                            'trip_type'=>($item['trip_type']!='')?$item['trip_type']:"--",
                            'status'=>($item['status']!='')?$item['status']:"0",
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
                $responseArray['data'] = $destination;
                $responseArray['dataTable'] = $dataTable;
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


    public function addDestination(Request $request){
        if($request->isMethod('post'))
        {
            $destinationData = $request->all();
            $destination = new Destination();
            $destination->title = $destinationData['title'];
            $destination->descriptions = $destinationData['description'];
            $destination->altitude = $destinationData['altitude'];
            $destination->climate = $destinationData['climate'];
            $destination->population = $destinationData['population'];
            if($destinationData['shopping']!=''){
                $destination->shopping = $destinationData['shopping'];
            }else{
                $destination->shopping = NULL;
            }

            if($destinationData['cuisine']!=''){
                $destination->cuisine = $destinationData['cuisine'];
            }else{
                $destination->cuisine = NULL;
            }

            if($destinationData['more']!=''){
                $destination->more_information = $destinationData['more'];
            }else{
                $destination->more_information = NULL;
            }
            $destination->trip_type = $destinationData['trip_type'];
            $destination->status = $destinationData['status'];

            if($destination->save()){
                $responseArray['status'] = 'success';
                $responseArray['code'] = '200';
                $responseArray['message'] = 'Destination Added Successfully.';
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





    public function updateDestination(Request $request){
        if($request->isMethod('post'))
        {
            $id = $request->get('id');
            $title = $request->get('title');
            $description = $request->get('description');
            $altitude = $request->get('altitude');
            $climate = $request->get('climate');
            $population = $request->get('population');
            $shopping = $request->get('shopping');
            $more = $request->get('more');
            $cuisine = $request->get('cuisine');
            $status = $request->get('status');

            $destination = Destination::find($id);
            $destination->title = $title;
            $destination->descriptions = $description;
            $destination->altitude = $altitude;
            $destination->climate = $climate;
            $destination->population = $population;
            
            if($shopping!=''){ 
                $destination->shopping = $shopping;
            }else{
                $destination->shopping = NULL;
            }

            if($more!=''){ 
                $destination->more_information = $more;
            }else{
                $destination->more_information = NULL;
            }

            if($cuisine!=''){ 
                $destination->cuisine = $cuisine;
            }else{
                $destination->cuisine = NULL;
            }   
            $destination->status = $status;
            
            if($destination->save()){
                $responseArray['status'] = 'success';
                $responseArray['code'] = '200';
                $responseArray['message'] = 'Destination Updated Successfully.';
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



    /*
     *@Destination Delete
     */
    public function destinationDelete(Request $request){
        if($request->isMethod('post'))
        {
            $id = $request->get('id');
            if(Destination::findOrFail($id)->delete()){
                //Delete All the Images realated to this destination

                $destinationGalleryList = DestinationGallery::where('destination_id','=',$id)->get();
                $count = $destinationGalleryList->count();
                $delItem = 0;
                foreach($destinationGalleryList as $item){
                    DestinationGallery::findOrFail($item['id'])->delete();     
                    $delItem++;    
                }
                if($delItem==$delItem){    
                    $responseArray['status'] = 'success';
                    $responseArray['code'] = '200';
                    $responseArray['message'] = 'Destination delete with images successfully.';
                }else{
                    $responseArray['status'] = 'success';
                    $responseArray['code'] = '200';
                    $responseArray['message'] = 'Destination delete with different images count successfully.';
                }
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



    //Upload Image Of the event
    public function imageupload(Request $request){
       $id= $request->get('id');
       $destinationDetails = Destination::find($id);
       $responseArray = array();
       if($request->get('imageStr')){
        foreach($request->get('imageStr') as $file){
                list($type, $data) = explode(';', $file);
                list(, $data)      = explode(',', $file);
                $datas = base64_decode($data);
                $typeArr = explode('/', $type);
                $file = md5(uniqid()) . '.'.end($typeArr);
                Storage::disk('destination')->put($file, base64_decode($data));
                if($this->saveDestinationImage($id,$file)){
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
                $responseArray['details']= $destinationDetails;
            }else{
                $responseArray['status'] = false;
                $responseArray['code']= "500";
                $responseArray['message']= "Opps! Somthing went wrong";
                return response()->json(['data' => $responseArray], $this->successStatus); 
            }
            
        }

        //Get Image List of this Event
        $imageList = DestinationGallery::where('destination_id','=',$id)->orderBy('id','DESC')->get();
        $imgGalleryList = array();
        foreach($imageList as $item){
            $imgGalleryList[] = array(
                "src"=>env('APP_URL').'/storage/app/public/destination/'.$item['image'],
                "thumbnail"=>env('APP_URL').'/storage/app/public/destination/'.$item['image'],
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
       $responseArray['details']= $destinationDetails;
       return response()->json(['data' => $responseArray], $this->successStatus); 
    }

    private function saveEventBannerImage($id,$imageName){
        $eventGallery = Destination::find($id);
        $eventGallery->banner = $imageName;
        if($eventGallery->save()){
            $lastId= $eventGallery->id;
            $this->resizeImage($lastId);
            return $eventGallery->id;
        }else{
            return false;
        }

    }




    private function resizeImage($id){
        $imageArr = DestinationGallery::find($id);
        $imageUrl = env('APP_URL').'/storage/app/public/destination/'.$imageArr['image'];
        $resize = \Image::make($imageUrl);
                
        // resize image to fixed size 1139X627
        $resize->resize(1139,627)->save($imageArr['image']);
        Storage::disk('destination/resize/1139X627')->put($imageArr['image'], $resize);

        // resize image to fixed size 372X253
        $resize->resize(372,253)->save($imageArr['image']);
        Storage::disk('destination/resize/372X253')->put($imageArr['image'], $resize);

         // resize image to fixed size 75X68
         $resize->resize(75,68)->save($imageArr['image']);
         Storage::disk('destination/resize/75X68')->put($imageArr['image'], $resize);
 

    }



    private function saveDestinationImage($id,$imageName){
        $eventGallery = new DestinationGallery();
        $eventGallery['destination_id'] = $id;
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




    public function getDestination(Request $request){
       $id= $request->get('id');
       $responseArray = array();
       if($request->isMethod('post')){
        $destination = Destination::find($id);
            $responseArray['status'] = 'success';
            $responseArray['code'] = '200';
            $responseArray['data'] = $destination;
       
       }else{
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['message'] = 'Invalid Requets';

        }
        return response()->json($responseArray, $this->successStatus); 

    }






  public function deleteDestinationImage(Request $request){
         $id = $request->get('id');
         $eventImage = DestinationGallery::find($id);
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



    public function defaultDestinationImage(Request $request){
    
         $id = $request->get('id');
         $eventImage = DestinationGallery::find($id);
         DB::table('destination_galleries')->update(['is_default' => 0]);
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




    

    public function updateDestinationImageStatus(Request $request){
    
         $id = $request->get('id');
         $eventImage = DestinationGallery::find($id);
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
