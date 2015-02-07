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
    return View::make('home');

});

Route::group(array('prefix' => getenv('api_prefix'), 'before' => 'oauth'), function(){
	Route::resource('series','SeriesController', array('only' => array('index', 'store', 'show', 'update', 'destroy')));
	Route::resource('comic','ComicsController', array('only' => array('index', 'store', 'show', 'update', 'destroy')));
    Route::resource('upload','UploadsController', array('only' => array('index', 'store', 'show', 'destroy')));
    Route::get('/image/{image_set_key}/{size?}', 'ComicImagesController@show');
	Route::get('comic/{comic_id}/meta', 'ComicsController@getMeta');
});

Route::post(getenv('api_prefix').'/oauth/access_token', function()
{
    //todo-mike: Make a filter to check params are all passed in...
    return Response::json(Authorizer::issueAccessToken());
});
Route::get('user', array('before' => 'oauth', function()
{
    return $ownerId = ResourceServer::getOwnerId()."\r\n".Auth::user();
}));
