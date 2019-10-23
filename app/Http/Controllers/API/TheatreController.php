<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MasterController;
use Auth;
use App\User;
use Session;
use App\Theatre;
use App\EventSeat;
use App\SittingType;

class TheatreController extends MasterController
{
    public $successStatus = 200;

    public function getTheatreList(Request $request){
        $responseArray = array();
        $ListArr = Theatre::paginate(100);
        $links = $ListArr->links();
        $responseArray['status'] = 'success';
        $responseArray['code'] = '200';
        $responseArray['theatre'] = $ListArr;
        return response()->json($responseArray, $this->successStatus); 
    }





    public function addNewTheatre(Request $request){
        if($request->method('post')){
            //Get all the data of the request
            $data  = $request->all();
            $theatreObj = new Theatre();
            $theatreObj->theater_name = $data['title'];
            $theatreObj->company_name = $data['company_name'];
            $theatreObj->contact_number = $data['contact_number'];
            $theatreObj->email_address = $data['email_address'];
            $theatreObj->country_id = $data['country'];
            $theatreObj->state_id = $data['state'];
            $theatreObj->city_id = $data['city'];
            $theatreObj->address = $data['address'];
            $theatreObj->zipcode = $data['zipcode'];
            $theatreObj->status = $data['status'];
            $theatreObj->created_at = self::getCreatedDate();
            if($theatreObj->save()){
                $responseArray['status'] = 'success';
                $responseArray['code'] = '200';
                $responseArray['message'] = "Theatre details saved";
            }else{
                $responseArray['status'] = 'error';
                $responseArray['code'] = '500';
                $responseArray['message'] = "Invalid request type";
            }
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['message'] = "Invalid request type";
        }
        return response()->json($responseArray, $this->successStatus); 
    }



    public function getTheatreById(Request $request){
        $responseArray = array();
        if($request->method('post')){
            $id  = $request->get('id');
            $theatreDetails = Theatre::find($id)->toArray();
            $responseArray['status']    = 'success';
            $responseArray['code']      = '200';
            $responseArray['message']   = "Update Theatre Details";
            $responseArray['theatre']   = $theatreDetails;
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['message'] = "No Theatre found";
        }
        return response()->json($responseArray, $this->successStatus); 
    }

    public function getTheatre(Request $request){
        $responseArray = array();

        if($request->method('post')){

            //Get Sitting Type 
            $sittingType = SittingType::where('status','=',1)->get();
            //Get all the data of the request
            $idStr  = $request->get('id');
            if($idStr!=''){
                $idStrArr = explode("|",$idStr);
                $id = $idStrArr[0];
                $sitting_type_id = $idStrArr[1];
                $theatreDetails = Theatre::with('EventSeat')->find($id)->toArray();
                if(!empty($theatreDetails)){
                    $responseArray['status']    = 'success';
                    $responseArray['code']      = '200';
                    $responseArray['message']   = "Update Theatre Details";
                    $responseArray['theatre']   = $theatreDetails;
                    $seatArr                    = array();
                    $seatArrObj                 = array();
                    $row                        = array();
                    $col                        = array();
                    //Calculate Seats
                    if(!empty($theatreDetails['event_seat'])){

                        foreach($theatreDetails['event_seat'] as $item){
                            if($item['sitting_type_id']==$sitting_type_id){
                                $row[$item['position_row']]=$item['position_row'];
                                $col[$item['position_column']]=$item['position_column'];
                
                                if($item['booking_status_id']==2){
                                    $key = 'seat_'.$item['position_row'].'_'.$item['position_column'];
                                    $seatArr['seat_'.$item['position_row'].'_'.$item['position_column']]='';
                                    $seatArrObj[]=array($key => '');
                                }else{
                                    $key = 'seat_'.$item['position_row'].'_'.$item['position_column'];
                                    $seatArr['seat_'.$item['position_row'].'_'.$item['position_column']]=$item['position_row'].'_'.$item['position_column'];
                                    $seatArrObj[]=array($key => $item['position_row'].'_'.$item['position_column']);
                                }
                            }
                        }
                        $responseArray['theatre'] = $theatreDetails;
                        $responseArray['row'] =  array('count'=>count($row),'row'=>$row);
                        $responseArray['col'] =  array('count'=>count($col),'row'=>$col);
                        $responseArray['seat'] = $seatArr;
                        $responseArray['seatArrObj'] = $seatArrObj;
                        $responseArray['sittingType'] = $sittingType;
                        
                    }else{
                        $responseArray['sittingType'] = $sittingType;
                        $responseArray['row'] =  array('count'=>0,'row'=>array());
                        $responseArray['col'] =  array('count'=>0,'row'=>array());
                        $responseArray['seat'] = array();
                    }
                   

                }else{
                    $responseArray['status'] = 'error';
                    $responseArray['code'] = '500';
                    $responseArray['message'] = "No Theatre found";
                }
            }else{
                $responseArray['status'] = 'error';
                $responseArray['code'] = '500';
                $responseArray['message'] = "Invalid request type";
            }
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['message'] = "Invalid request type";
        }
        return response()->json($responseArray, $this->successStatus); 

    }





    public function updateTheatre(Request $request){
        if($request->method('post')){
            //Get all the data of the request
            $data  = $request->all();
            $theatreId = $data['id'];
            $theatreObj = Theatre::find($theatreId);
            if(empty($theatreObj)){ 
                $responseArray['status'] = 'error';
                $responseArray['code'] = '500';
                $responseArray['message'] = "No Theatre found";
                return response()->json($responseArray, $this->successStatus); 
            }
            $theatreObj->theater_name = $data['title'];
            $theatreObj->company_name = $data['company_name'];
            $theatreObj->contact_number = $data['contact_number'];
            $theatreObj->email_address = $data['email_address'];
            $theatreObj->country_id = $data['country'];
            $theatreObj->state_id = $data['state'];
            $theatreObj->city_id = $data['city'];
            $theatreObj->address = $data['address'];
            $theatreObj->zipcode = $data['zipcode'];
            $theatreObj->status = $data['status'];
            $theatreObj->created_at = self::getCreatedDate();
            if($theatreObj->save()){
                $responseArray['status'] = 'success';
                $responseArray['code'] = '200';
                $responseArray['message'] = "Theatre details updated successfully";
            }else{
                $responseArray['status'] = 'error';
                $responseArray['code'] = '500';
                $responseArray['message'] = "Invalid request type";
            }
        }else{
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['message'] = "Invalid request type";
        }
        return response()->json($responseArray, $this->successStatus); 
    }




    public function updateTheatreSeat(Request $request){
        if($request->isMethod('post')){
            $data   = $request->all();
            $id    = $request->get('id');
            $row    = $request->get('row');
            $col    = $request->get('col');
            $seat   = $request->get('seat');
            $sitting_type_id   = $request->get('sitting_type_id');

            //Update All Seat for this Theater
            $seatObj = EventSeat::where('theatre_id','=',$id)->get();
            if(!empty($seatObj)){
                EventSeat::where('sitting_type_id', '=', $sitting_type_id)->where('theatre_id', '=', $id)->delete();
            }
            foreach($seat as $k=>$v){
                $theatreSeat = new EventSeat(); 
                $theatreSeat->theatre_id        = $id;
                $theatreSeat->sitting_type_id   = $sitting_type_id;

                //get Positions Row and Position Coloum
                $seatName = explode("_",$k);
                $theatreSeat->position_row = $seatName[1];
                $theatreSeat->position_column = $seatName[2];
                if($v==''){
                    $theatreSeat->booking_status_id = '2';
                }else{
                    $theatreSeat->booking_status_id = '3';
                }
                $theatreSeat->created_at        = self::getCreatedDate();
                $theatreSeat->save();
            }
            $responseArray['status'] = 'success';
            $responseArray['code'] = '200';
            $responseArray['message'] = "Theatre details updated successfully";

        }else{
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['message'] = "Invalid request type";
        }
        return response()->json($responseArray, $this->successStatus); 

    }





    public function getTheatreSeat(Request $request){
        $seatArr = array();
        $row     = array();
        $col     = array();

        $id= $request->get('id');
        $seatObj = EventSeat::where('theatre_id','=',$id)->get();
        if(($seatObj->count())>0){
            foreach($seatObj as $item){
                $row[$item['position_row']]=$item['position_row'];
                $col[$item['position_column']]=$item['position_column'];

                if($item['booking_status_id']==2){
                    $seatArr['seat_'.$item['position_row'].'_'.$item['position_column']]='';
                }else{
                    $seatArr['seat_'.$item['position_row'].'_'.$item['position_column']]=$item['position_row'].'_'.$item['position_column'];
                }
            }
            $responseArray['status'] = 'success';
            $responseArray['code'] = '200';
            $responseArray['row'] =  array('count'=>count($row),'row'=>$row);
            $responseArray['col'] =  array('count'=>count($col),'row'=>$col);
            $responseArray['seat'] = $seatArr;
            $responseArray['seat'] = $seatArr;
        }else
        {
            $responseArray['status'] = 'error';
            $responseArray['code'] = '500';
            $responseArray['message'] = "No Seat Found for this theater.";
        }
        return response()->json($responseArray, $this->successStatus); 
    }









}
