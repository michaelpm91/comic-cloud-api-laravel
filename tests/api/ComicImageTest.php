<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 04/04/15
 * Time: 00:08
 */

use Laracasts\TestDummy\Factory;

use App\Models\Upload; //TODO: Remove this
use App\Models\User;
use App\Models\ComicImage;

class ComicImageTest extends ApiTester {

    protected $user;
    protected $auth_header;
    protected $comic_image_endpoint = "/v0.1/images/";

    public function setUp(){//runs per test :(
        parent::setUp();
        Artisan::call('db:seed');//TODO: Would be nice to move this...
        $this->user = User::find(1);
    }
    /**
     * @group image-test
     */
    public function test_it_must_be_authenticated(){
        //arrange
        $this->test_access_token = "";

        //act
        $response = $this->getRequest($this->comic_image_endpoint.str_random(40));

        //assert
        $this->assertResponseStatus(401);

    }
    /**
     * @group image-test
     */
    public function test_it_only_accepts_get_requests_to_a_specific_image(){
        //arrange
        $img_slug = str_random(40);
        $img_json =  json_encode([1 => $img_slug]);

        $upload = Factory::create('App\Models\Upload', [
            'user_id' =>  $this->user->id
        ]);

        $cba = Factory::create('App\Models\ComicBookArchive', [
            'upload_id' => $upload->id,
            'comic_book_archive_hash' => $img_json
        ]);

        $comic = Factory::create('App\Models\Comic', [
            'user_id' => $this->user->id,
            'comic_book_archive_contents' => $img_json,
            'comic_book_archive_id' => $cba->id
        ]);



        $imageentry = new ComicImage;
        $imageentry->image_slug = $img_slug;
        $imageentry->image_hash = str_random(32);
        $imageentry->image_size = rand(5000, 150000);
        $imageentry->image_url = 'http://www.dogster.com/wp-content/uploads/2015/05/doge.jpg';
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

    }
    /**
     * @group image-test
     */
    public function test_it_will_404_when_requests_are_made_to_an_image_with_no_url_parameter(){//TODO: Revisit as IMAGE route now exists
        $this->markTestSkipped('This test will fail as new routs exist');

        //arrange

        //act
        $response = $this->postRequest($this->comic_image_endpoint);

        //assert
        //TODO:Should also assert JSON
        $this->assertResponseStatus(404);


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
    /**
     * @group image-test
     */
    public function test_it_fetches_image(){//Retrieve single image
        //arrange
        $img_slug = str_random(40);
        $img_json =  json_encode([1 => $img_slug]);

        $upload = Factory::create('App\Models\Upload', [
            'user_id' =>  $this->user->id
        ]);

        $cba = Factory::create('App\Models\ComicBookArchive', [
            'upload_id' => $upload->id,
            'comic_book_archive_hash' => $img_json
        ]);

        $comic = Factory::create('App\Models\Comic', [
            'user_id' => $this->user->id,
            'comic_book_archive_contents' => $img_json,
            'comic_book_archive_id' => $cba->id
        ]);


        $imageentry = new ComicImage;
        $imageentry->image_slug = $img_slug;
        $imageentry->image_hash = str_random(32);
        $imageentry->image_size = rand(5000, 150000);
        $imageentry->image_url = 'http://www.dogster.com/wp-content/uploads/2015/05/doge.jpg';
        $imageentry->save();
        $imageentry->comicBookArchives()->attach($cba->id);//Pivot table needed which aren't currently support by Test Dummy :(

        //act
        $response = $this->getRequest($this->comic_image_endpoint.$img_slug);

        //assert
        $this->assertResponseOk();


    }
    /**
     * @group image-test
     */
    public function test_it_cannot_fetch_an_image_that_does_not_exist(){
        //arrange

        //act
        $response = $this->getRequest($this->comic_image_endpoint.str_random(40));

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    /**
     * @group image-test
     */
    public function test_it_fetches_user_comic_image_only(){//
        //arrange
        $img_slug = str_random(40);
        $img_json =  json_encode([1 => $img_slug]);

        $upload = Factory::create('App\Models\Upload', [
            'user_id.id' =>  2
        ]);

        $cba = Factory::create('App\Models\ComicBookArchive', [
            'upload_id' => $upload->id,
            'comic_book_archive_hash' => $img_json
        ]);


        $comic = Factory::create('App\Models\Comic', [
            'user_id' => 2,
            'comic_book_archive_contents' => $img_json,
            'comic_book_archive_id' => $cba->id
        ]);


        $imageentry = new ComicImage;
        $imageentry->image_slug = $img_slug;
        $imageentry->image_hash = str_random(32);
        $imageentry->image_size = rand(5000, 150000);
        $imageentry->image_url = 'http://www.dogster.com/wp-content/uploads/2015/05/doge.jpg';
        $imageentry->save();
        $imageentry->comicBookArchives()->attach($cba->id);//Pivot table needed which aren't currently support by Test Dummy :(

        //act
        $response = $this->getRequest($this->comic_image_endpoint.$img_slug);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent


    }

}
 