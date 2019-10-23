<?php
namespace App\Http\Controllers\Api;
#require '../../'.__DIR__ . '/vendor/autoload.php';
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MasterController;
use Auth;
use App\User;
use Session;
use App\State;
use App\City;
use App\DeliveryAddress;
use App\StoreType;
use Log;
use App\Setting;
// Use the REST API Client to make requests to the Twilio REST API
use Twilio\Rest\Client;
use Promocodes;


class ApiController extends MasterController
{
     

    public function generatecode(Request $request){
        //  $code = Promocodes::output($amount = 1);
        $data = [];
        //Promocodes::create($amount = 1, $reward = 500, [], $expires_in = "2019-31-08", $quantity = 10, $is_disposable = false);
        $code ="C5EC-6GAT";
        Promocodes::redeem($code);
        //Promocodes::create(1, 25, ['foo' => 'bar', 'baz' => 'qux']);
        //dd($code);
    }
     /**
     *@Author: Pradeep Kumar
     *@Description: Login Authentication Page
     */
   
    public function gettoken(Request $request) {
        $params = $request->all();
        //dd($params);
        $str='';
        foreach($params as $key=>$val){
            if($key!='token'){
                $str.=$val.'|'; 
            }
        }
        //echo $str.config('app.app_salt'); DIE;
        return md5($str.config('app.app_salt'));
        
    }


    public function getStateList(Request $request){
        try{
            $params = $request->all();
            $stateObj = new State();
            $state= $stateObj->getAllState();
            $responseArray['status'] = true;
            $responseArray['data'] =$state;
            
        }catch (Exception $e) {
            $responseArray['status'] = false;
            $responseArray['message'] = $e->getMessage();
        }
        return response()->json($responseArray);

    }



    public function getSettingList(Request $request){
        try{
            $settingArr = Setting::all();
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


    
    public function settingUpdate(Request $request){
        try{
            $params = $request->all();
            $settingData = $request->get('body');
            $countItme = count($settingData);
            $updatecount = 1;
            
            foreach($settingData as $key=>$val){
                $nameStr = explode('__',$key);
                $id = end($nameStr);
                $settingObj = Setting::find($id);
                $settingObj['options_value'] = $val;
                try{
                    $settingObj->save();
                    $updatecount++;
                }catch (Exception $e) {
                    continue;
                }
            }     
            if($countItme==($updatecount-1)){   
                $responseArray['status'] = true;
                $responseArray['code'] = 200;
                $responseArray['message'] ="Setting Data updated successfully.";
            }else{
                $responseArray['status'] = false;
                $responseArray['code'] = 500;
                $responseArray['message'] ="Setting Data not updated.";
            }
            
        }catch (Exception $e) {
            $responseArray['status'] = false;
            $responseArray['message'] = $e->getMessage();
        }
        return response()->json($responseArray);

    }




}