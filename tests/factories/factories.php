<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 22/03/15
 * Time: 19:12
 */

$factory('App\User', [
    'username' => $faker->username,
    'email' => $faker->email,
    'password' => $faker->password
]);

$factory('App\Upload', function ($faker){
    $fileExt = ['cbz', 'cbr', 'pdf',];
    $thisFileExt = $fileExt[rand(0,2)];
    $fileName = implode(' ', $faker->words(rand(3,7))).'.'.$thisFileExt;

    return[
        'user_id'  => 'factory:App\User',
        'file_original_name' => $fileName,
        'file_size' => rand(1000000, 50000000),
        'file_upload_name' => str_random(40).'.'.$thisFileExt
    ];
});

$factory('App\Comic', [

]);

$factory('App\Series', [

]);

$factory('App\ComicImage', [

]);