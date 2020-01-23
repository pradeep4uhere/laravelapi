<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Darryldecode\Cart\CartCondition;
use Auth;
use File;
use Session;
use Config;
use Log;


class MasterController extends Controller {

    public $default_lang='en';
    protected $responseArray = array();

    
    public static function getCreatedDate(){
        return date('Y-m-d H:i:s');
    }


    /**
     *@Author: Pradeep Kumar
     *@Description: Login Authentication Page
     */
    public static function isValidToekn($request){
    try{
	        $parameters = $request->all();
	        $str='';
	        $token='';
	        if(!empty($parameters)){
	            foreach($parameters as $key=>$val){
	                if($key!='token'){
	                    $str.=$val.'|'; 
	                }else{
	                    $token = $val;
	                }
				}
				//echo $token; '--';
	            //echo $str.config('global.CLIENT_SECRET'); die;
	           	$serverTotak = sha1($str.config('app.app_salt')); 
	            if($token==$serverTotak){
	                return true;
	            }else{
	                return false;
	            }
	        }else{
	            return false;
	        }
	    }catch (Exception $e) {
	        return false;
	    }
	    
    }




    public static function getInvalidTokenMsg(){
    	$responseArray = array();
    	$responseArray['status'] = 'error';
	    $responseArray['code'] = '500';
	    $responseArray['message'] = "!! Invalid token !!";
	    return  $responseArray;

    }











}
