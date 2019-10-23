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

}
