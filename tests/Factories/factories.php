<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 22/03/15
 * Time: 17:02
 */

//$factory;

//$faker;

$factory('App\Thread', [
    'user_id' => 1,
    'file_original_name' => $faker->word(3).'.cbz',
    'file_size' => rand(5000, 150000),
    'file_upload_name' => str_random(40).'.cbz'
]);