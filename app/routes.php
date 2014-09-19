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

Route::group(array('prefix' => getenv('api_prefix'), 'before' => 'oauth'/*'auth.basic'*//*'auth.basic.once'*/), function(){
	Route::resource('series','SeriesController', array('only' => array('index', 'store', 'show', 'update', 'destroy')));
	Route::resource('comic','ComicsController', array('only' => array('index', 'store', 'show', 'update', 'destroy')));
    Route::resource('upload','UploadsController', array('only' => array('index', 'store', 'show', 'destroy')));
    Route::get('/image/{image_set_key}/{size?}', 'ComicImagesController@show');
	Route::get('comic/{comic_id}/meta', 'ComicsController@getMeta');
});

Route::post('oauth/access_token', function()
{
    //return AuthorizationServer::performAccessTokenFlow();
    //return Response::json(Authorizer::issueAccessToken());
    return Response::json(AuthorizationServer::issueAccessToken());
});

