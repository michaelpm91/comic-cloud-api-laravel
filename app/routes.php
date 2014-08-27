<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
    /*$user = new User;
    $user->email = 'michaelpm91@googlemail.com';
    $user->password = Hash::make('1234');
    $user->save();*/
	return View::make('hello');
});

Route::group(array('prefix' => 'api/v1', 'before' => /*'auth.basic'*/'auth.basic.once'), function(){
	Route::resource('series','SeriesController');
	Route::resource('comic','ComicsController');
    Route::resource('upload','UploadsController');
});


