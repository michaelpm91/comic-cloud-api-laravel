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
    return View::make('home');

});

Route::group(array('before' => 'oauth'), function() {
    Route::resource('upload','UploadsController', array('only' => array('index', 'store', 'show')));
    Route::resource('series','SeriesController', array('only' => array('index', 'store', 'show', 'update', 'destroy')));
    Route::resource('comic','ComicsController', array('only' => array('index', 'store', 'show', 'update', 'destroy')));
    Route::get('image/{image_set_key}/{size?}', 'ComicImagesController@show');
    Route::get('comic/{comic_id}/meta', 'ComicsController@getMeta');
});

Route::post('oauth/access_token', function () {
    return Response::json(Authorizer::issueAccessToken());
});

Route::controllers([
    'auth' => 'Auth\AuthController',
    //'password' => 'Auth\PasswordController',//temporarily disabled
]);

//Route::post('register', 'AuthController@register');