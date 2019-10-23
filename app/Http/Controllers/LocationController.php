<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\MasterController;
use App\Location;

class LocationController extends MasterController
{

    public $successStatus = 200;

    

    public function testPdf(Request $request){
        return view('testpdf');

    }


    public function updatestatus(Request $request,$id, $status){
        if($id>0){
            $tatus = $status;
            $location = Location::find($id);
            $location->status = $status;
            if($location->save()){
                return Redirect::back()->withErrors(['message', 'Status Updated']);
            }else{
                return Redirect::back()->withErrors(['error', 'Status Not Updated']);
            }
        }

    }


     public function deletelocation(Request $request,$id){
        if($id>0){
            $location = Location::find($id);
            if($location->delete()){
                return Redirect::back()->withErrors(['message', 'Status Updated']);
            }else{
                return Redirect::back()->withErrors(['error', 'Status Not Updated']);
            }
        }

    }



    public function addLocation(Request $request){
        if($request->isMethod('post')){
            $location = new Location();
            $location->parent_id = $request->get('parent_id');
            $location->location_name = $request->get('location_name');
            $location->status = 1;
            $location->created_at = date('Y-m-d h:i:s');
            if( $location->save()){
                return Redirect::back()->withErrors(['message', 'Status Added']);
            }else{
                return Redirect::back()->withErrors(['error', 'Status Not Added']);
            }
        }
    }

}
