<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 04/04/15
 * Time: 21:31
 */

use Laracasts\TestDummy\Factory;

use App\Models\User;
use App\Models\Series;

use Rhumsaa\Uuid\Uuid;

class SeriesTest extends ApiTester {


    protected $auth_header;
    protected $user;
    protected $series_endpoint = "/v0.1/series/";

	public function setUp(){//runs per test :(
        parent::setUp();
        Artisan::call('db:seed');//TODO: Would be nice to move this...
        $this->user = User::find(1);
    }
    /**
	* @group series-test
	*/
	public function test_it_must_be_authenticated(){
        //arrange
        $this->test_access_token = "";

        //act
        $response = $this->getRequest($this->series_endpoint);

        //assert
        $this->assertResponseStatus(401);

    }
    /**
	* @group series-test
	*/
	public function test_it_does_not_accept_post_requests_to_a_specific_series(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $response = $this->postRequest($this->series_endpoint.$comic->series->id);

        //assert
        //TODO:Should also assert JSON
        $this->assertResponseStatus(405);
    }
    /**
	* @group series-test
	*/
	public function test_it_can_create_a_new_series_for_a_comic(){
        $this->markTestSkipped('This test will fail as API relationship representations have changed');

        //arrange
        $comic = Factory::create('App\Models\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $newSeriesId = Uuid::uuid4();
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
    /**
	* @group series-test
	*/
	public function test_it_cannot_create_a_series_without_an_id(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
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
    /**
	* @group series-test
	*/
	public function test_it_cannot_create_a_series_without_a_comic_id(){//aka orphan_series
        //arrange
        $comic = Factory::create('App\Models\Comic', [
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
    /**
	* @group series-test
	*/
	public function test_it_cannot_create_a_series_without_a_series_title(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
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
    /**
	* @group series-test
	*/
	public function test_it_can_create_a_series_with_a_start_year(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $newSeriesId = Uuid::uuid4();
        $response = $this->postRequest($this->series_endpoint, [
            'id' => $newSeriesId,
            'comic_id' => $comic->id,
            'series_title' => 'Test Title 1',
            'series_start_year' => '1991'
        ]);

        //assert
        $this->assertResponseStatus(201);

    }
    /**
	* @group series-test
	*/
	public function test_it_can_create_a_series_without_a_start_year(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $newSeriesId = Uuid::uuid4();
        $response = $this->postRequest($this->series_endpoint, [
            'id' => $newSeriesId,
            'comic_id' => $comic->id,
            'series_title' => 'Test Title 1',
            'series_start_year' => '1991'
        ]);

        //assert
        $this->assertResponseStatus(201);

    }
    /**
	* @group series-test
	*/
	public function test_it_will_generate_a_new_series_id_when_a_duplicate_is_passed_when_creating_a_new_series (){} //Related to client ID generation issue
    /**
	* @group series-test
	*/
	public function test_it_can_fetch_all_series(){
        //arrange
        $mocked_comics = Factory::times(10)->create('App\Models\Comic', [
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
    /**
	* @group series-test
	*/
	public function test_it_can_fetch_a_specific_series(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $response = $this->getRequest($this->series_endpoint.$comic->series->id);

        //assert
        $this->assertResponseOk();
        $this->assertEquals($comic->series->id, head(json_decode($response, true)['series'])['id']);
        $this->assertEquals($comic->series->series_title, head(json_decode($response, true)['series'])['series_title']);

    }
    /**
	* @group series-test
	*/
	public function test_it_cannot_fetch_a_series_that_does_not_exist(){
        //arrange

        //act
        $response = $this->getRequest($this->series_endpoint.str_random(40));

        //assert
        $this->assertResponseStatus(404);

    }
    /**
	* @group series-test
	*/
	public function test_it_cannot_fetch_a_series_that_does_not_belong_to_the_user(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
            'user_id.id' => 2,
            'series_id.user_id' => 2
        ]);

        //act
        $response = $this->getRequest($this->series_endpoint.$comic->series->id);

        //assert
        $this->assertResponseStatus(404);
    }
    /**
	* @group series-test
	*/
	public function test_it_can_update_a_series_title(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
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


        $this->assertEquals($comic->series->id, head(json_decode($response, true)['series'])['id']);
        $this->assertEquals('Test Series Title', head(json_decode($response, true)['series'])['series_title']);

    }
    /**
	* @group series-test
	*/
	public function test_it_can_update_a_series_start_year(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
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


        $this->assertEquals($comic->series->id, head(json_decode($response, true)['series'])['id']);
        $this->assertEquals(1991, head(json_decode($response, true)['series'])['series_start_year']);

    }
    /**
	* @group series-test
	*/
	public function test_it_can_update_a_series_publisher(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
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


        $this->assertEquals($comic->series->id, head(json_decode($response, true)['series'])['id']);
        $this->assertEquals('Test Series Publisher', head(json_decode($response, true)['series'])['series_publisher']);

    }
    /**
	* @group series-test
	*/
	public function test_it_cannot_update_a_series_title_that_does_not_belong_to_the_user(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
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
    /**
	* @group series-test
	*/
	public function test_it_cannot_update_a_series_start_year_that_does_not_belong_to_the_user(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
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
    /**
	* @group series-test
	*/
	public function test_it_cannot_update_a_series_publisher_that_does_not_belong_to_the_user(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
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
    /**
	* @group series-test
	*/
	public function test_it_can_delete_a_series(){
        //arrange
        $mocked_series = Factory::create('App\Models\Series', ['user_id' => $this->user->id]);
        $mocked_comics = Factory::times(10)->create('App\Models\Comic', [
            'user_id' => $this->user->id,
            'series_id' => $mocked_series->id
        ]);

        //act
        $response = $this->deleteRequest($this->series_endpoint . $mocked_series->id);

        //assert
        $this->assertResponseOk();
    }
    /**
	* @group series-test
	*/
	public function test_it_can_delete_a_series_and_associated_comics(){
        //arrange
        $mocked_series = Factory::create('App\Models\Series', ['user_id' => $this->user->id]);
        $mocked_comics = Factory::times(10)->create('App\Models\Comic', [
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
    /**
	* @group series-test
	*/
	public function test_it_cannot_delete_a_series_that_does_not_belong_to_the_user(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
            'user_id.id' => 2,
            'series_id.user_id' => 2
        ]);

        //act
        $response = $this->deleteRequest($this->series_endpoint.$comic->series->id);

        //assert
        $this->assertResponseStatus(404);
    }
    /**
	* @group series-test
	*/
	public function test_it_cannot_delete_a_series_that_does_not_exist(){
        //arrange

        //act
        $response = $this->deleteRequest($this->series_endpoint.str_random(40));

        //assert
        $this->assertResponseStatus(404);
    }
    /**
     * @vcr comicvine-series.yml
     */
    /**
	* @group series-test
	*/
	public function test_it_can_fetch_meta_data_for_a_series_that_exists(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id,
            'series_id.series_title' => 'All Star Superman',
        ]);
        //act
        $response = $this->getRequest($this->series_endpoint.$comic->series->id."/meta");

        //assert
        $this->assertResponseOk();
    }
    /**
	* @group series-test
	*/
	public function test_it_cannot_fetch_meta_data_for_a_series_that_does_not_exist(){
        //arrange
        $series_id = str_random(40);

        //act
        $response = $this->getRequest($this->series_endpoint.$series_id."/meta");

        //assert
        $this->assertResponseStatus(404);

    }
    /**
	* @group series-test
	*/
	public function test_it_cannot_fetch_meta_data_for_series_that_does_not_belong_to_the_user(){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
            'user_id.id' => 2,
            'series_id.user_id' => 2,
            'series_id.series_title' => 'All Star Superman',
        ]);
        //act
        $response = $this->getRequest($this->series_endpoint.$comic->series->id."/meta");

        //assert
        $this->assertResponseStatus(404);

    }
    /**
     * @vcr comicvine-series.yml
     */
    /**
	* @group series-test
	*/
	public function test_it_can_set_a_comic_vine_series_id_on_a_series_that_exists(){

        //arrange
        $comic = Factory::create('App\Models\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id,
            'series_id.series_title' => 'All Star Superman',
        ]);
        //act
        $response = $this->getRequest($this->series_endpoint.$comic->series->id."/meta");

        //assert
        $this->assertResponseOk();

        //arrange
        $comic_vine_series_id = json_decode($response, true)['series'][0]['comic_vine_series_id'];

        //act
        $response = $this->patchRequest($this->series_endpoint.$comic->series->id, [
            'comic_vine_series_id' => $comic_vine_series_id
        ]);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest($this->series_endpoint.$comic->series->id);

        //assert
        $response_comic_vine_series_id = head(json_decode($response, true)['series'])['comic_vine_series_id'];
        $this->assertEquals($response_comic_vine_series_id, $comic_vine_series_id);

    }
    /**
	* @group series-test
	*/
	public function test_a_comic_vine_series_id_must_be_numerical (){
        //arrange
        $comic = Factory::create('App\Models\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id,
            'series_id.series_title' => 'All Star Superman',
        ]);

        //arrange
        $comic_vine_series_id = str_random();

        //act
        $response = $this->patchRequest($this->series_endpoint.$comic->series->id, [
            'comic_vine_series_id' => $comic_vine_series_id
        ]);

        //assert
        $this->assertResponseStatus(400);
    }
    /**
     * @vcr comicvine-series.yml
     */
    /**
	* @group series-test
	*/
	public function test_it_cannot_set_a_comic_vine_series_id_on_a_series_that_does_not_exist(){

        //arrange
        $series_id = str_random(40);
        $comic_vine_series_id = rand(10000, 99999);

        //act
        $response = $this->patchRequest($this->series_endpoint.$series_id, [
            'comic_vine_series_id' => $comic_vine_series_id
        ]);

        //assert
        $this->assertResponseStatus(404);

    }
    /**
	* @group series-test
	*/
	public function test_it_cannot_set_a_comic_vine_series_id_on_a_series_that_does_not_belong_to_the_user(){

        //arrange
        $other_user_comic = Factory::create('App\Models\Comic', [
            'user_id.id' => 2,
            'series_id.user_id' => 2,
            'series_id.series_title' => 'All Star Superman',
        ]);
        $comic_vine_series_id = rand(10000, 99999);


        //act
        $response = $this->patchRequest($this->series_endpoint.$other_user_comic->series->id, [
            'comic_vine_series_id' => $comic_vine_series_id
        ]);

        //assert
        $this->assertResponseStatus(404);

    }
}
 