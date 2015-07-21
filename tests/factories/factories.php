<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 22/03/15
 * Time: 19:12
 */

use Rhumsaa\Uuid\Uuid;

$factory('App\User', [
    'username' => $faker->username,
    'email' => $faker->email,
    'password' => $faker->password
]);

$factory('App\Upload', function ($faker){
    $fileExt = ['cbz', 'cbr', 'pdf',];
    $thisFileExt = $fileExt[rand(0,2)];
    $fileName = implode(' ', $faker->words(rand(3,7))).'.'.$thisFileExt;
    $random_upload_id = Uuid::uuid4();

    return[
        'user_id'  => 'factory:App\User',
        'file_original_name' => $fileName,
        'file_size' => rand(1000000, 50000000),
        'file_upload_name' => $random_upload_id.'.'.$thisFileExt,
        'file_original_file_type' => $thisFileExt,
        'file_random_upload_id' => $random_upload_id,
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

$factory('App\Comic', function($faker){
    $id = Uuid::uuid4();
    return [
        'id' => $id,
        'comic_issue' => rand(1, 99),
        'comic_writer' => $faker->name,
        'comic_book_archive_contents' => json_encode([
            1 => Uuid::uuid4().".jpg",
            2 => Uuid::uuid4().".jpg",
            3 => Uuid::uuid4().".jpg",
            4 => Uuid::uuid4().".jpg",
            5 => Uuid::uuid4().".jpg",
            6 => Uuid::uuid4().".jpg",
        ]),
        'user_id' => 'factory:App\User',
        'series_id' => 'factory:App\Series',
        'comic_vine_issue_id' => '',
        'comic_book_archive_id' => 'factory:App\ComicBookArchive',
        'comic_status' => 1
    ];
});

$factory('App\Series', function($faker){
    $id = Uuid::uuid4();
    return [
        'id' => $id,
        'series_title' => $faker->sentence(),
        'series_start_year' => rand(1900, 2015),
        'series_publisher' => 'Unknown',
        'comic_vine_series_id' => '',
        'user_id' => 'factory:App\User'
    ];
});

$factory('App\ComicBookArchive', [
    'upload_id' => 'factory:App\Upload',
    'comic_book_archive_contents' => '',
    'comic_book_archive_hash' => json_encode([
        1 => Uuid::uuid4().".jpg",
        2 => Uuid::uuid4().".jpg",
        3 => Uuid::uuid4().".jpg",
        4 => Uuid::uuid4().".jpg",
        5 => Uuid::uuid4().".jpg",
        6 => Uuid::uuid4().".jpg",
    ]),
    'comic_book_archive_status' => 1
]);

$factory('App\ComicImage', [
    'image_slug' => '',
    'image_size' => '',
    'image_hash' => '',
    'image_url' => 'http://www.dogster.com/wp-content/uploads/2015/05/doge.jpg'
]);