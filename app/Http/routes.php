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

Route::group(['before' => 'oauth:basic', 'prefix' => 'v'.env('APP_API_VERSION')], function() {
    Route::resource('uploads','UploadsController', array('only' => array('index', 'store', 'show')));
    Route::resource('series','SeriesController', array('only' => array('index', 'store', 'show', 'update', 'destroy')));
    Route::resource('comics','ComicsController', array('only' => array('index', 'show', 'update', 'destroy')));
    Route::get('images/{image_slug}/{size?}', 'ComicImagesController@show');
    Route::get('comics/{comic_id}/series', 'ComicsController@showRelatedSeries');
    Route::get('series/{series_id}/meta', 'SeriesController@showMetaData');//TODO: Should these be filters?
    Route::get('series/{series_id}/comics', 'SeriesController@showRelatedComics');
    Route::get('comics/{comic_id}/meta', 'ComicsController@showMetaData');//TODO: Should these be filters?
    Route::get('uploads/{upload_id}/download','UploadsController@download');//TODO: Is this needed anymore?
});

Route::group(['before' => 'oauth:processor', 'prefix' => 'v'.env('APP_API_VERSION')], function() {
    Route::get('images', 'ComicImagesController@index');
    Route::post('images', 'ComicImagesController@store');
    Route::put('comicbookarchives/{cba_id}', 'ComicBookArchivesController@update');
    Route::get('comicbookarchives/{cba_id}', 'ComicBookArchivesController@show');
});

Route::group(['prefix' => 'v'.env('APP_API_VERSION')], function() {
    Route::post('auth/register', 'AuthController@store');
    Route::post('oauth/access_token', function () {//TODO:Mode to Auth Controller
        return Response::json(Authorizer::issueAccessToken());
    });
});

/*Route::controllers([
    'auth' => 'Auth\AuthController',
    //'password' => 'Auth\PasswordController' //temporarily disabled
]);*/
