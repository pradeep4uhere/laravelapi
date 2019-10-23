<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return "<center><h1>Rudra Admin Panel</h1></center>"; die;
});
Route::get('/linknow', function () {
	if(Artisan::call('cache:clear')){
    	return "Cache is cleared";
	}
});

Route::get('test','LocationController@testPdf')->name('test');
Route::get('updatestatus/{id}/{status}','LocationController@updatestatus')->name('updatestatus');
Route::get('deletelocation/{id}','LocationController@deletelocation')->name('deletelocation');
Route::get('addnewlocation','LocationController@addnewlocation')->name('addnewlocation');
Route::any('addLocation','LocationController@addLocation')->name('addLocation');

