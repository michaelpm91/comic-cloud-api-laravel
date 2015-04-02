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
        'file_upload_name' => str_random(40).'.'.$thisFileExt,
        'match_data' => json_encode([
            'exists' => false,
            'series_id' => 'factory:App\Series',
            'comic_id' => 'factory:App\Comic',
            'series_title' => $faker->sentence,
            'series_start_year' => rand(1900, 2015),
            'comic_issue' => rand(1, 99)
        ])
    ];
});

$factory('App\Comic', [
    'id' => str_random(40),
    'comic_issue' => rand(1, 99),
    'comic_writer' => $faker->name,
    'comic_book_archive_contents' => '',
    'user_id' => 'factory:App\User',
    'series_id' => 'factory:App\Series',
    'comic_book_archive_id' => 'factory:App\ComicBookArchive',
    'comic_status' => 0
]);

$factory('App\Series', [
    'id' => str_random(40),
    'series_title' => $faker->sentence(),
    'series_start_year' => rand(1900, 2015),
    'series_publisher' => 'unknown',
    'user_id' => 'factory:App\User'
]);

$factory('App\ComicBookArchiv', [
    'upload_id' => 'factory:App\Upload',
    'comic_book_archive_contents' => '',
    'comic_book_archive_hash' => str_random(20),
    'comic_book_archive_status' => 0
]);

$factory('App\ComicImage', [

]);