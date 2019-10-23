<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MasterController;
use Auth;
use App\User;
use App\Setting;
use Session;

class SettingController extends MasterController
{

    public function test(Request $request) {
       return response()->json(array("Hello"));
    }


     public function getSetting(Request $request) {
        $setting = Setting::all();
        return response()->json($setting);
    }

}
