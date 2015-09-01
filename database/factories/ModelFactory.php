<?php


/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    $types = ['basic', 'admin'];
    return [
        'username' => $faker->username,
        'email' => $faker->email,
        'type' => $types[rand(0,1)],
        'password' => $faker->password
    ];
});

$factory->define(App\Models\Upload::class, function (Faker\Generator $faker) {

    $fileExt = ['cbz', 'cbr', 'pdf',];
    $thisFileExt = $fileExt[rand(0,2)];
    $fileName = implode(' ', $faker->words(rand(3,7))).'.'.$thisFileExt;
    $random_upload_id = $faker->uuid;

    return[
        'user_id'  => factory(App\Models\User::class)->create()->id,
        'file_original_name' => $fileName,
        'file_size' => $faker->numberBetween(1000000, 50000000),
        'file_upload_name' => $random_upload_id.'.'.$thisFileExt,
        'file_original_file_type' => $thisFileExt,
        'file_random_upload_id' => $random_upload_id,
        'match_data' => json_encode([
            'exists' => false,
            'series_id' =>  $faker->uuid,
            'comic_id' => $faker->uuid,
            'series_title' => $faker->sentence,
            'series_start_year' => $faker->year,
            'comic_issue' => $faker->numberBetween(1,999)
        ])
    ];
});

$factory->define(App\Models\Comic::class, function (Faker\Generator $faker) {
    $cba = factory(App\Models\ComicBookArchive::class)->create();

    $comic_book_archive_contents = [];

    $comic_image = factory(App\Models\ComicImage::class, rand(5,20))->create();
    $comic_image->each(function($i, $k)use($cba, &$comic_book_archive_contents){
        $i->comicBookArchives()->attach($cba->id);
        $key = ($k + 1);
        $comic_book_archive_contents[$key] = $i->image_slug;
    });

    $cba->comic_book_archive_contents = json_encode($comic_book_archive_contents);
    $cba->save();

    return [
        'id' => $faker->uuid,
        'comic_issue' => (string)$faker->numberBetween(1,999),
        'comic_writer' => $faker->name,
        'comic_book_archive_contents' => json_encode($comic_book_archive_contents),
        'user_id'  => factory(App\Models\User::class)->create()->id,
        'series_id' =>  factory(App\Models\Series::class)->create()->id,
        'comic_vine_issue_id' => (string)$faker->randomNumber(),
        'comic_book_archive_id' =>  $cba->id
    ];
});

$factory->define(App\Models\Series::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->uuid,
        'series_title' => $faker->sentence(),
        'series_start_year' => $faker->year,
        'series_publisher' => 'Unknown',
        'comic_vine_series_id' => (string)$faker->randomNumber(),
        'user_id'  => factory(App\Models\User::class)->create()->id,
    ];
});

$factory->define(App\Models\ComicBookArchive::class, function (Faker\Generator $faker) {
    return [
        'upload_id' => factory(App\Models\Upload::class)->create()->id,
        'comic_book_archive_hash' => $faker->md5,
        'comic_book_archive_contents' => null,
        'comic_book_archive_status' => 0
    ];
});

$factory->define(App\Models\ComicImage::class, function (Faker\Generator $faker) {
    return [
        'image_slug' => $faker->uuid,
        'image_size' => $faker->numberBetween(1000000, 50000000),
        'image_hash' => $faker->md5,
        'image_url' => $faker->imageUrl(600, 960, 'cats')
    ];
});