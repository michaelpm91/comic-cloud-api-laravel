<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function()
{
    return "<h1 style='font-family:Arial;'>".env('APP_URL')."-".env('APP_API_VERSION')."</h1>";

});

Route::get('/status', function()
{
    return json_encode('OK');

});

Route::group(array('before' => 'oauth'), function() {
    Route::resource('upload','UploadsController', array('only' => array('index', 'store', 'show')));
    Route::resource('series','SeriesController', array('only' => array('index', 'store', 'show', 'update', 'destroy')));
    Route::resource('comic','ComicsController', array('only' => array('index', 'show', 'update', 'destroy')));
    Route::get('image/{image_set_key}/{size?}', 'ComicImagesController@show');
    Route::get('series/{series_id}/meta', 'SeriesController@getMeta');
    Route::get('comic/{comic_id}/meta', 'ComicsController@getMeta');
});

Route::post('auth/register', 'AuthController@store');
Route::post('oauth/access_token', function () {//TODO:Mode to Auth Controller
    return Response::json(Authorizer::issueAccessToken());
});

/*Route::controllers([
    'auth' => 'Auth\AuthController',
    //'password' => 'Auth\PasswordController' //temporarily disabled
]);*/
