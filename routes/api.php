<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::get('test'         , 'API\SettingController@test');


//Open API For Get Methods Only
Route::group(['prefix'=>'en/v1/', 'middleware' => ['cors']],function () {
	Route::get('getSetting'         , 'API\SettingController@getSetting');
    Route::post('register'          , 'API\UserController@register');
    Route::post('login'             , 'API\UserController@login')->name('login');
    Route::get('gettoken'           , 'API\ApiController@getToken')->name('gettoken');
    Route::any('getsetting'         , 'API\ApiController@getSettingList')->name('getsetting');
    Route::any('settingupdate'      , 'API\ApiController@settingUpdate')->name('settingupdate');
    Route::any('addtocart'          , 'API\CartController@addToCart')->name('addtocart');
    Route::any('addtoexpcart'       , 'API\CartController@addtoExpCart')->name('addtoexpcart');
    


    
});

//Auth API
Route::group(['prefix'=>'en/v1/', 'middleware' => ['auth:api','cors']],function () {

    Route::any('getalldashboard'    , 'API\GeneralController@getAllDashobardList');
    Route::any('getalltravellorderlist'    , 'API\GeneralController@getAllTravellOrderList');
    

	Route::any('testauth'           , 'API\GeneralController@testAPI');
    Route::get('details'            , 'API\UserController@details');
    Route::any('getuserlist'        , 'API\UserController@getUserList');
    Route::any('updateuser'         , 'API\UserController@userupdate');
    Route::any('usereventorderlist' , 'API\UserController@userEventOrderList');
    Route::any('usereventorderdetails' , 'API\UserController@userEventOrderDetails');
    Route::any('usertravelorderdetails' , 'API\UserController@userTravelOrderDetails');
    


    
    //All Event Realted API Start Hrer
    Route::any('geteventlist'       , 'API\EventController@getEventList');
    Route::any('addevent'           , 'API\EventController@addEvent');
    Route::any('updateevent'        , 'API\EventController@updateEvent');
    

    Route::any('geteventdetails'    , 'API\EventController@getEventDetails');
    Route::any('eventgetbanner'     , 'API\EventController@getEventBanner');
    Route::any('saveeventbanner'    , 'API\EventController@saveEventBanner');
    Route::any('saveeventdetails'   , 'API\EventController@saveEventDetails');
    Route::any('geteventlocation'   , 'API\EventController@getEventLocation');
    Route::any('updateeventtiming'  , 'API\EventController@updateEventTiming');
    Route::any('deleteeventtiming'  , 'API\EventController@deleteEventTiming');
    Route::any('eventimagedelete'   , 'API\EventController@deleteEventImage');
    Route::any('eventimagedefault'  , 'API\EventController@defaultEventImage');
    Route::any('updateeventstatusimage'  , 'API\EventController@updateEventImageStatus');
    Route::any('updateeventstatusfeatureimage'  , 'API\EventController@updateEventFeatureImageStatus');
    Route::any('deleteevent'        , 'API\EventController@deleteEvent');

    
    Route::any('getitinerary'           , 'API\ItineraryController@getitinerary');
    Route::any('additinerary'           , 'API\ItineraryController@addItinerary');
    Route::any('allitinerary'           , 'API\ItineraryController@allItinerary');
    Route::any('itineraryimageupload'   , 'API\ItineraryController@imageupload');
    Route::any('itineraryimagedefault'  , 'API\ItineraryController@defaultImage');
    Route::any('itineraryimagedelete'   , 'API\ItineraryController@deleteImage');
    Route::any('itinerarydeparture'     , 'API\ItineraryController@itineraryDepartureUpdate');
    Route::any('departuredelete'        , 'API\ItineraryController@itineraryDepartureDelete');
    Route::any('itinerarydelete'        , 'API\ItineraryController@itineraryDelete');
    Route::any('itinerarydayslist'      , 'API\ItineraryController@itineraryDaysList');
    Route::any('additinerarydays'       , 'API\ItineraryController@addItineraryDays');
    Route::any('deleteitineraryday'     , 'API\ItineraryController@deleteItineraryDays');
    Route::any('itinerarydayimageupload', 'API\ItineraryController@dayImageUpload');
    Route::any('itinerarydayimagedefault'  , 'API\ItineraryController@defaultdayImage');
    Route::any('itinerarydayimagedelete'   , 'API\ItineraryController@deletedayImage');
    
    
    









    //Upload Image
    Route::any('imageupload'        , 'API\EventController@imageupload');

    //Home Page Banner
    Route::any('bannerimageupload'  , 'API\GeneralController@imageupload');
    Route::any('bannerimagedelete'   , 'API\GeneralController@deleteBannerImage');
    Route::any('bannerimagedefault'  , 'API\GeneralController@defaultBannerImage');
    Route::any('updatebannerstatusimage'  , 'API\GeneralController@updateBannerImageStatus');


    //All theatre type API start here
    Route::any('gettheatrelist'     , 'API\TheatreController@getTheatreList');
    Route::any('addtheatre'         , 'API\TheatreController@addNewTheatre');
    Route::any('gettheatre'         , 'API\TheatreController@getTheatre');
    Route::any('updatetheatre'      , 'API\TheatreController@updateTheatre');
    Route::any('updatetheatreseat'  , 'API\TheatreController@updateTheatreSeat');
    Route::any('gettheatreseat'     , 'API\TheatreController@getTheatreSeat');
    Route::any('gettheatrebyid'     , 'API\TheatreController@getTheatreById');


    //All Global Setting Type Here
    Route::any('addseat'            , 'API\GeneralController@addseat');
    Route::any('updateseat'         , 'API\GeneralController@updateseat');
    Route::any('getseattinglist'    , 'API\GeneralController@getseattinglist');

    Route::any('getpagelist'        , 'API\GeneralController@getpagelist');
    Route::any('getpagedetails'     , 'API\GeneralController@getpagedetails');
    Route::any('pagedetailupdate'   , 'API\GeneralController@pagedetailupdate');
    Route::any('eventbannerupload'  , 'API\EventController@eventBannerUpload');

    Route::any('getmembership'      , 'API\GeneralController@getMembership');

    Route::any('updateviedos'       , 'API\GeneralController@updateViedos');
    Route::any('getviedos'          , 'API\GeneralController@getViedoList');
    Route::any('deleteviedos'       , 'API\GeneralController@deleteViedos');
    Route::any('deleteviedos'       , 'API\GeneralController@deleteViedos');


    Route::any('adddestination'     , 'API\DestinationController@addDestination');
    Route::any('alldestination'     , 'API\DestinationController@allDestination');
    Route::any('destinationimageupload', 'API\DestinationController@imageupload');
    Route::any('destinationdelete'  , 'API\DestinationController@destinationDelete');
    Route::any('getdestination'     , 'API\DestinationController@getDestination');
    Route::any('updatedestination'  , 'API\DestinationController@updateDestination');
    Route::any('destinationimagedelete'   , 'API\DestinationController@deleteDestinationImage');
    Route::any('destinationimagedefault'  , 'API\DestinationController@defaultDestinationImage');
    Route::any('updatedestinationstatusimage'  , 'API\DestinationController@updateDestinationImageStatus');






});



Route::group(['prefix'=>'front/en/v1/', 'middleware' => ['cors']],function () {
    Route::post('register'            , 'API\UserController@register');
    Route::post('login'               , 'API\UserController@login')->name('login');
    Route::any('populareventlist'     , 'API\FrontController@popularEventList');
    Route::any('getsetting'           , 'API\FrontController@getSettingList')->name('getsetting');
    Route::any('eventdetails'         , 'API\FrontController@getEventDetails')->name('eventdetails');
    Route::any('addtocart'            , 'API\CartController@addToCart')->name('addtocart');
    Route::any('getcartlist'          , 'API\CartController@getCartList')->name('getcartlist');
    Route::any('deleteitemfrom'       , 'API\CartController@removeItemFromCartList')->name('deleteitemfrom');
    Route::any('updateitemcart'       , 'API\CartController@updateItemFromCartList')->name('updateitemcart');
    Route::any('updateexpitemcart'    , 'API\CartController@updateExpItemFromCartList')->name('updateexpitemcart');
    Route::any('checkoffer'           , 'API\CartController@checkOfferCode')->name('checkoffer');
    Route::any('prepaymentbooking'    , 'API\OrderController@prePaymentBooking')->name('prepaymentbooking');
    Route::any('expressbooking'       , 'API\OrderController@prePaymentExpBooking')->name('expressbooking');
    Route::any('getdestinationlist'   , 'API\FrontController@getDestinationList')->name('getdestinationlist');
    Route::any('getbannerlist'        , 'API\FrontController@getBannerList')->name('getbannerlist');
    Route::any('getalleventlist'      , 'API\FrontController@getAllEventList')->name('getalleventlist');
    Route::any('getdestinationexplist', 'API\FrontController@getDestinationExpList')->name('getdestinationexplist');
    Route::any('addtoexpcart'         , 'API\CartController@addtoExpCart')->name('addtoexpcart');
    Route::any('getexpcartlist'       , 'API\CartController@getExpCartList')->name('getexpcartlist');
    Route::any('lastorderlist'        , 'API\OrderController@getLastOrderList')->name('lastorderlist');
    Route::any('getcityname'          , 'API\FrontController@getCityName')->name('getcityname');
    Route::any('usereventorderlist'   , 'API\UserController@userEventOrderList')->name('usereventorderlist');
    Route::any('userdetails'          , 'API\UserController@userDetails')->name('userdetails');
    Route::any('updateuser'           , 'API\UserController@userUpdateProfile')->name('updateuser');
    Route::any('usereventorderdetails', 'API\UserController@userEventOrderDetails');
    Route::any('getallstate'          , 'API\FrontController@getAllState')->name('getallstate');
    Route::any('getallcity'          , 'API\FrontController@getAllCity')->name('getallcity');
    
    
    
});










