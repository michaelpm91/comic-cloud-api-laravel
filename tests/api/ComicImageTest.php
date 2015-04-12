<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 04/04/15
 * Time: 00:08
 */

use Laracasts\TestDummy\Factory;

use App\Upload; //TODO: Remove this
use App\User;
use App\ComicImage;

class ComicImageTest extends ApiTester {

    protected $user;
    protected $auth_header;
    protected $comic_image_endpoint = "/image/";

    public function setUp(){//runs per test :(
        parent::setUp();
        Artisan::call('db:seed');//TODO: Would be nice to move this...
        $this->user = User::find(1);
    }
    public function test_it_must_be_authenticated(){
        //arrange
        $this->test_access_token = "";

        //act
        $response = $this->getRequest($this->comic_image_endpoint.str_random(40));

        //assert
        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are madem ore consistent

    }
    public function test_it_only_accepts_get_requests_to_a_specific_image(){
        //arrange
        $img_slug = str_random(40);
        $img_json =  json_encode([1 => $img_slug]);

        $upload = Factory::create('App\Upload', [
            'user_id' =>  $this->user->id
        ]);

        $cba = Factory::create('App\ComicBookArchive', [
            'upload_id' => $upload->id,
            'comic_book_archive_hash' => $img_json
        ]);

        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'comic_book_archive_contents' => $img_json,
            'comic_book_archive_id' => $cba->id
        ]);

        $img = file_get_contents(storage_path().'/test files/black-image-comic-page.jpg');

        Storage::disk(env('user_images'))->put($img_slug.".jpg", $img);


        $imageentry = new ComicImage;
        $imageentry->image_slug = $img_slug;
        $imageentry->image_hash = hash_file('md5', $img);
        $imageentry->image_size = rand(5000, 150000);
        $imageentry->save();
        $imageentry->comicBookArchives()->attach($cba->id);//Pivot table needed which aren't currently support by Test Dummy :(

        //act
        $response = $this->postRequest($this->comic_image_endpoint.$img_slug);

        //assert
        //TODO:Should also assert JSON
        $this->assertResponseStatus(405);


        //act
        $response = $this->patchRequest($this->comic_image_endpoint.$img_slug);

        //assert
        //TODO:Should also assert JSON
        $this->assertResponseStatus(405);

        //act
        $response = $this->deleteRequest($this->comic_image_endpoint.$img_slug);

        //assert
        //TODO:Should also assert JSON
        $this->assertResponseStatus(405);



        Storage::disk(env('user_images'))->delete($img_slug.".jpg");
    }
    public function test_it_will_404_when_requests_are_made_to_an_image_with_no_url_parameter(){
        //arrange

        //act
        $response = $this->postRequest($this->comic_image_endpoint);

        //assert
        //TODO:Should also assert JSON
        $this->assertResponseStatus(404);


        //act
        $response = $this->patchRequest($this->comic_image_endpoint);

        //assert
        //TODO:Should also assert JSON
        $this->assertResponseStatus(404);

        //act
        $response = $this->deleteRequest($this->comic_image_endpoint);

        //assert
        //TODO:Should also assert JSON
        $this->assertResponseStatus(404);

        //act
        $response = $this->getRequest($this->comic_image_endpoint);

        //assert

        $this->assertResponseStatus(404);

    }
    public function test_it_fetches_image(){//Retrieve single image
        //arrange
        $img_slug = str_random(40);
        $img_json =  json_encode([1 => $img_slug]);

        $upload = Factory::create('App\Upload', [
            'user_id' =>  $this->user->id
        ]);

        $cba = Factory::create('App\ComicBookArchive', [
            'upload_id' => $upload->id,
            'comic_book_archive_hash' => $img_json
        ]);

        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'comic_book_archive_contents' => $img_json,
            'comic_book_archive_id' => $cba->id
        ]);

        $img = file_get_contents(storage_path().'/test files/black-image-comic-page.jpg');

        Storage::disk(env('user_images'))->put($img_slug.".jpg", $img);


        $imageentry = new ComicImage;
        $imageentry->image_slug = $img_slug;
        $imageentry->image_hash = hash_file('md5', $img);
        $imageentry->image_size = rand(5000, 150000);
        $imageentry->save();
        $imageentry->comicBookArchives()->attach($cba->id);//Pivot table needed which aren't currently support by Test Dummy :(

        //act
        $response = $this->getRequest($this->comic_image_endpoint.$img_slug);

        //assert
        $this->assertResponseOk();

        Storage::disk(env('user_images'))->delete($img_slug.".jpg");

    }
    public function test_it_cannot_fetch_an_image_that_does_not_exist(){
        //arrange

        //act
        $response = $this->getRequest($this->comic_image_endpoint.str_random(40));

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_fetches_user_comic_image_only(){//
        //arrange
        $img_slug = str_random(40);
        $img_json =  json_encode([1 => $img_slug]);

        $upload = Factory::create('App\Upload', [
            'user_id.id' =>  2
        ]);

        $cba = Factory::create('App\ComicBookArchive', [
            'upload_id' => $upload->id,
            'comic_book_archive_hash' => $img_json
        ]);


        $comic = Factory::create('App\Comic', [
            'user_id' => 2,
            'comic_book_archive_contents' => $img_json,
            'comic_book_archive_id' => $cba->id
        ]);

        $img = file_get_contents(storage_path().'/test files/black-image-comic-page.jpg');

        Storage::disk(env('user_images'))->put($img_slug.".jpg", $img);


        $imageentry = new ComicImage;
        $imageentry->image_slug = $img_slug;
        $imageentry->image_hash = hash_file('md5', $img);
        $imageentry->image_size = rand(5000, 150000);
        $imageentry->save();
        $imageentry->comicBookArchives()->attach($cba->id);//Pivot table needed which aren't currently support by Test Dummy :(

        //act
        $response = $this->getRequest($this->comic_image_endpoint.$img_slug);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

        Storage::disk(env('user_images'))->delete($img_slug.".jpg");

    }

}
 