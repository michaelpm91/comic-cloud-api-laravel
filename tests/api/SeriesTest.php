<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 04/04/15
 * Time: 21:31
 */

use Laracasts\TestDummy\Factory;

use App\User;
use App\Series;

class SeriesTest extends ApiTester {


    protected $auth_header;
    protected $user;
    protected $series_endpoint = "/series/";

    public function setUp(){//runs per test :(
        parent::setUp();
        Artisan::call('db:seed');//TODO: Would be nice to move this...
        $this->user = User::find(1);
    }
    public function test_it_must_be_authenticated(){
        //arrange
        $this->test_access_token = "";

        //act
        $response = $this->getRequest($this->series_endpoint);

        //assert
        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_does_not_accept_post_requests_to_a_specific_series(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $response = $this->postRequest($this->series_endpoint.$comic->series->id);

        //assert
        //TODO:Should also assert JSON
        $this->assertResponseStatus(405);
    }
    public function test_it_can_create_a_new_series_for_a_comic(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $newSeriesId = str_random(40);
        $response = $this->postRequest($this->series_endpoint, [
            'id' => $newSeriesId,
            'comic_id' => $comic->id,
            'series_title' => 'Test Title 1',
            'series_start_year' => '1991'
        ]);

        //assert
        $this->assertResponseStatus(201);

        //act
        $response = $this->getRequest($this->series_endpoint.$newSeriesId);

        //assert
        $this->assertResponseOk();

        //assert
        $response_comic_id = json_decode($response, true)['series']['comics'][0]['id'];
        $this->assertEquals($response_comic_id, $comic->id);

    }
    public function test_it_cannot_create_a_series_without_an_id(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $response = $this->postRequest($this->series_endpoint, [
            'comic_id' => $comic->id,
            'series_title' => 'Test Title 1',
            'series_start_year' => '1991'
        ]);

        //assert
        $this->assertResponseStatus(400);

    }
    public function test_it_cannot_create_a_series_without_a_comic_id(){//aka orphan_series
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $newSeriesId = str_random(40);
        $response = $this->postRequest($this->series_endpoint, [
            'id' => $newSeriesId,
            'series_title' => 'Test Title 1',
            'series_start_year' => '1991'
        ]);

        //assert
        $this->assertResponseStatus(400);

    }
    public function test_it_cannot_create_a_series_without_a_series_title(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $newSeriesId = str_random(40);
        $response = $this->postRequest($this->series_endpoint, [
            'id' => $newSeriesId,
            'comic_id' => $comic->id,
            'series_start_year' => '1991'
        ]);

        //assert
        $this->assertResponseStatus(400);

    }
    public function test_it_can_create_a_series_with_a_start_year(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $newSeriesId = str_random(40);
        $response = $this->postRequest($this->series_endpoint, [
            'id' => $newSeriesId,
            'comic_id' => $comic->id,
            'series_title' => 'Test Title 1',
            'series_start_year' => '1991'
        ]);

        //assert
        $this->assertResponseStatus(201);

    }
    public function test_it_can_create_a_series_without_a_start_year(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $newSeriesId = str_random(40);
        $response = $this->postRequest($this->series_endpoint, [
            'id' => $newSeriesId,
            'comic_id' => $comic->id,
            'series_title' => 'Test Title 1',
            'series_start_year' => '1991'
        ]);

        //assert
        $this->assertResponseStatus(201);

    }
    //public function test_it_will_generate_a_new_series_id_when_a_duplicate_is_passed_when_creating_a_new_series (){} //Related to client ID generation issue
    public function test_it_can_fetch_all_series(){
        //arrange
        $mocked_comics = Factory::times(10)->create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $response = $this->getRequest($this->series_endpoint);

        //assert
        $result = true;
        foreach($mocked_comics as $mocked_comic ){
            if (!in_array($mocked_comic->series->id, json_decode($response, true)['series'])) {
                $result = false;
                break;
            }
        }
        $this->assertResponseOk();
        $this->assertEquals(false, $result);
    }
    public function test_it_can_fetch_a_specific_series(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $response = $this->getRequest($this->series_endpoint.$comic->series->id);

        //assert
        $this->assertResponseOk();
        $this->assertEquals($comic->series->id, json_decode($response, true)['series']['id']);
        $this->assertEquals($comic->series->series_title, json_decode($response, true)['series']['series_title']);

    }
    public function test_it_cannot_fetch_a_series_that_does_not_exist(){
        //arrange

        //act
        $response = $this->getRequest($this->series_endpoint.str_random(40));

        //assert
        $this->assertResponseStatus(404);

    }
    public function test_it_cannot_fetch_a_series_that_does_not_belong_to_the_user(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id.id' => 2,
            'series_id.user_id' => 2
        ]);

        //act
        $response = $this->getRequest($this->series_endpoint.$comic->series->id);

        //assert
        $this->assertResponseStatus(404);
    }
    public function test_it_can_update_a_series_title(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $response = $this->patchRequest($this->series_endpoint.$comic->series->id, [
            'series_title' => 'Test Series Title'
        ]);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest($this->series_endpoint.$comic->series->id);

        //assert
        $this->assertResponseOk();


        $this->assertEquals($comic->series->id, json_decode($response, true)['series']['id']);
        $this->assertEquals('Test Series Title', json_decode($response, true)['series']['series_title']);

    }
    public function test_it_can_update_a_series_start_year(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $response = $this->patchRequest($this->series_endpoint.$comic->series->id, [
            'series_start_year' => 1991
        ]);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest($this->series_endpoint.$comic->series->id);

        //assert
        $this->assertResponseOk();


        $this->assertEquals($comic->series->id, json_decode($response, true)['series']['id']);
        $this->assertEquals(1991, json_decode($response, true)['series']['series_start_year']);

    }
    public function test_it_can_update_a_series_publisher(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $response = $this->patchRequest($this->series_endpoint.$comic->series->id, [
            'series_publisher' => 'Test Series Publisher'
        ]);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest($this->series_endpoint.$comic->series->id);

        //assert
        $this->assertResponseOk();


        $this->assertEquals($comic->series->id, json_decode($response, true)['series']['id']);
        $this->assertEquals('Test Series Publisher', json_decode($response, true)['series']['series_publisher']);

    }
    public function test_it_cannot_update_a_series_title_that_does_not_belong_to_the_user(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id.id' => 2,
            'series_id.user_id' => 2
        ]);

        //act
        $response = $this->patchRequest($this->series_endpoint.$comic->series->id, [
            'series_title' => 'Test Series Title'
        ]);

        //assert
        $this->assertResponseStatus(404);

    }
    public function test_it_cannot_update_a_series_start_year_that_does_not_belong_to_the_user(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id.id' => 2,
            'series_id.user_id' => 2
        ]);

        //act
        $response = $this->patchRequest($this->series_endpoint.$comic->series->id, [
            'series_start_year' => 1991
        ]);

        //assert
        $this->assertResponseStatus(404);

    }
    public function test_it_cannot_update_a_series_publisher_that_does_not_belong_to_the_user(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id.id' => 2,
            'series_id.user_id' => 2
        ]);

        //act
        $response = $this->patchRequest($this->series_endpoint.$comic->series->id, [
            'series_publisher' => 'Test Series Publisher'
        ]);

        //assert
        $this->assertResponseStatus(404);

    }
    public function test_it_can_delete_a_series(){
        //arrange
        $mocked_series = Factory::create('App\Series', ['user_id' => $this->user->id]);
        $mocked_comics = Factory::times(10)->create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id' => $mocked_series->id
        ]);

        //act
        $response = $this->deleteRequest($this->series_endpoint . $mocked_series->id);

        //assert
        $this->assertResponseOk();
    }
    public function test_it_can_delete_a_series_and_associated_comics(){
        //arrange
        $mocked_series = Factory::create('App\Series', ['user_id' => $this->user->id]);
        $mocked_comics = Factory::times(10)->create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id' => $mocked_series->id
        ]);

        //act
        $response = $this->deleteRequest($this->series_endpoint.$mocked_series->id);

        //assert
        $this->assertResponseOk();

        foreach($mocked_comics as $mocked_comic ){
            //act
            $this->getRequest('/comic/'.$mocked_comic->id);
            //assert
            $this->assertResponseStatus(404);
        }


    }
    public function test_it_cannot_delete_a_series_that_does_not_belong_to_the_user(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id.id' => 2,
            'series_id.user_id' => 2
        ]);

        //act
        $response = $this->deleteRequest($this->series_endpoint.$comic->series->id);

        //assert
        $this->assertResponseStatus(404);
    }
    public function test_it_cannot_delete_a_series_that_does_not_exist(){
        //arrange

        //act
        $response = $this->deleteRequest($this->series_endpoint.str_random(40));

        //assert
        $this->assertResponseStatus(404);
    }
    /**
     * @group specific
     * @vcr comicvine-series.yml
     */
    public function test_it_can_fetch_meta_data_for_a_comic_that_exists(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id,
            'series_id.series_title' => 'All Star Superman'
        ]);
        //act
        //$response = $this->getRequest($this->series_endpoint.$comic->id."/meta");

        //assert
        //$this->assertResponseOk();
    }
}
 