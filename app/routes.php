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
    /*return View::make('hello');*/
    return 'API Home.';

});

Route::group(array('prefix' => getenv('api_prefix'), 'before' => /*'auth.basic'*/'auth.basic.once'), function(){
	Route::resource('series','SeriesController', array('only' => array('index', 'store', 'show', 'update', 'destroy')));
	Route::resource('comic','ComicsController', array('only' => array('index', 'store', 'show', 'update', 'destroy')));
    Route::resource('upload','UploadsController', array('only' => array('index', 'store', 'show', 'destroy')));
    //Route::resource('image','ComicImagesController');
    //Route::get('/image/{image_set_key}/{size?}', array(/*'as' => 'name.stuff.here',*/ 'uses' => 'ComicImagesController@show'));
	Route::get('/image/{image_set_key}/{size?}', 'ComicImagesController@show');
	Route::get('comic/{comic_id}/meta', 'ComicsController@getMeta');
});



