<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 20/03/15
 * Time: 18:16
 */

use App\Comic;
use App\User;
use Laracasts\TestDummy\Factory;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;


class ComicTest extends ApiTester {

    protected $user;
    protected $auth_header;
    protected $comic_endpoint = "/comics/";
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
        $response = $this->getRequest($this->comic_endpoint);

        //assert
        $this->assertResponseStatus(401);

    }
    public function test_it_does_not_accept_post_requests(){
        //act
        $response = $this->postRequest($this->comic_endpoint);

        //assert
        //TODO:Should also assert JSON
        $this->assertResponseStatus(405);
    }
    public function test_it_fetches_all_comics(){//Retrieve all user comics
        //arrange
        $mocked_comics = Factory::times(10)->create('App\Comic', ['user_id' => $this->user->id]);

        //act
        $response = $this->getRequest($this->comic_endpoint);

        //assert
        $result = true;
        foreach($mocked_comics as $mocked_comic ){
            $mocked_id = $mocked_comic->id;
            if (!in_array($mocked_comic->id, json_decode($response, true)['comics'])) {
                $result = false;
                break;
            }
        }
        $this->assertEquals(false, $result);

    }
    public function test_it_fetches_comic(){//Retrieve single comic
        //arrange
        $comic = Factory::create('App\Comic', ['user_id' => $this->user->id]);

        //act
        $response = $this->getRequest($this->comic_endpoint.$comic->id);

        //assert
        $this->assertResponseOk();

    }
    public function test_it_cannot_fetch_a_comic_that_does_not_exist(){
        //arrange

        //act
        $response = $this->getRequest($this->comic_endpoint.str_random(40));

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_fetches_user_comics_only(){//
        //arrange
        $user_comics = Factory::times(5)->create('App\Comic', ['user_id' => $this->user->id]);
        $other_user_comic = Factory::create('App\Comic', ['user_id' => 2]);

        //act
        $response = $this->getRequest($this->comic_endpoint);

        //assert
        $result = false;
        foreach(json_decode($response, true)['comics'] as $comic){
            if($comic['id'] == $other_user_comic->id) $result = true;
        }
        $this->assertEquals(false, $result);

    }
    public function test_it_fetches_user_comic_only(){//
        //arrange
        $comic = Factory::create('App\Comic', ['user_id' => 2]);

        //act
        $response = $this->getRequest($this->comic_endpoint.$comic->id);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_can_edit_a_comic_comic_writer(){//TODO:Multiple Asserts...
        //arrange
        $comic = Factory::create('App\Comic', ['user_id' => $this->user->id]);

        //act
        $response = $this->patchRequest($this->comic_endpoint.$comic->id, [
            'comic_writer' => 'John Smith'
        ]);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest($this->comic_endpoint.$comic->id);

        //assert
        $this->assertEquals('John Smith', json_decode($response, true)['comic']['comic_writer']);


    }
    public function test_it_can_edit_a_comic_comic_issue(){
        //arrange
        $comic = Factory::create('App\Comic', ['user_id' => $this->user->id]);

        //act
        $response = $this->patchRequest($this->comic_endpoint.$comic->id, [
            'comic_issue' => 1
        ]);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest($this->comic_endpoint.$comic->id);

        //assert
        $this->assertEquals(1, json_decode($response, true)['comic']['comic_issue']);
    }
    public function test_it_can_set_a_comic_series_id_to_one_that_exists(){
        //arrange
        $comic_a = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);
        $comic_b = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $response = $this->patchRequest($this->comic_endpoint.$comic_a->id, [
            'series_id' => $comic_b->series->id
        ]);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest($this->comic_endpoint.$comic_a->id);

        //assert
        $this->assertEquals($comic_b->series->id, json_decode($response, true)['comic']['series']['id']);

    }
    public function test_it_cannot_set_a_comic_series_id_to_one_that_the_user_does_not_own(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);
        $other_user_comic = Factory::create('App\Comic');

        //act
        $response = $this->patchRequest($this->comic_endpoint.$comic->id, [
            'series_id' => $other_user_comic->series->id
        ]);

        //assert
        $this->assertResponseStatus(400);


    }
    public function test_it_cannot_set_a_comic_series_id_to_one_that_does_not_exist(){

        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);

        //act
        $response = $this->patchRequest($this->comic_endpoint.$comic->id, [
            'series_id' => str_random(40)
        ]);

        //assert
        $this->assertResponseStatus(400);

    }
    public function test_it_cannot_edit_another_users_comic(){

        //arrange
        $otherusercomic = Factory::create('App\Comic', ['user_id' => 2]);

        //act
        $response = $this->patchRequest($this->comic_endpoint.$otherusercomic->id, [
            'comic_issue' => 1
        ]);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_cannot_edit_a_comic_comic_issue_that_does_not_exist(){
        //arrange

        //act
        $response = $this->patchRequest($this->comic_endpoint.str_random(40), [
            'comic_issue' => 1
        ]);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_cannot_edit_a_comic_comic_writer_that_does_not_exist(){
        //arrange

        //act
        $response = $this->patchRequest($this->comic_endpoint.str_random(40), [
            'comic_writer' => 'John Smith'
        ]);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_returns_an_appropriate_message_when_no_edit_fields_are_entered(){
        //arrange
        $comic = Factory::create('App\Comic', ['user_id' => $this->user->id]);
        //act
        $response = $this->patchRequest($this->comic_endpoint.$comic->id);

        //assert
        $this->assertResponseStatus(400);

    }
    public function test_it_can_delete_a_comic(){
        //arrange
        $comic = Factory::create('App\Comic', ['user_id' => $this->user->id]);

        //act
        $response = $this->deleteRequest($this->comic_endpoint.$comic->id);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest($this->comic_endpoint.$comic->id);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_cannot_delete_a_comic_that_does_not_exist(){
        //arrange

        //act
        $response = $this->deleteRequest($this->comic_endpoint.str_random(40));

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent


    }
    public function test_it_cannot_delete_another_users_comic(){
        //arrange
        $otherusercomic = Factory::create('App\Comic', ['user_id' => 2]);

        //act
        $response = $this->deleteRequest($this->comic_endpoint.$otherusercomic->id);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_will_delete_a_series_if_the_last_comic_has_been_deleted(){
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id
        ]);
        $series_id = $comic->series->id;

        //act
        $response = $this->getRequest($this->series_endpoint.$comic->series->id);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->deleteRequest($this->comic_endpoint.$comic->id);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest($this->series_endpoint.$series_id);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    /**
     * @vcr comicvine-comic.yml
     */
    public function test_it_can_fetch_meta_data_for_a_comic_that_exists(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id,
            'series_id.series_title' => 'All Star Superman',
            'series_id.comic_vine_series_id' => '18139'
        ]);
        //act
        $response = $this->getRequest($this->comic_endpoint.$comic->id."/meta");

        //assert
        $this->assertResponseOk();
    }
    /**
     * @vcr comicvine-comic.yml
     */
    public function test_it_cannot_fetch_meta_data_for_a_comic_that_does_not_exist(){
        //arrange
        $comic_id = str_random(40);
        //act
        $response = $this->getRequest($this->comic_endpoint.$comic_id."/meta");

        //assert
        $this->assertResponseStatus(404);
    }
    /**
     * @vcr comicvine-comic.yml
     */
    public function test_it_can_set_a_comic_vine_comic_id_on_a_comic_that_exists(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id,
            'series_id.series_title' => 'All Star Superman',
            'series_id.comic_vine_series_id' => '18139'
        ]);
        //act
        $response = $this->getRequest($this->comic_endpoint.$comic->id."/meta");
        $comic_vine_issue_id = json_decode($response, true)['issues'][0]['comic_vine_issue_id'];

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->patchRequest($this->comic_endpoint.$comic->id, [
            'comic_vine_issue_id' => $comic_vine_issue_id
        ]);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest($this->comic_endpoint.$comic->id);

        //assert
        $this->assertEquals($comic_vine_issue_id, json_decode($response, true)['comic']['comic_vine_issue_id']);
    }
    /**
     * @vcr comicvine-comic.yml
     */
    public function test_it_cannot_set_a_comic_vine_comic_id_on_a_comic_that_does_not_exist(){
        //arrange
        $comic_id = str_random(40);
        $comic_vine_issue_id = rand(10000, 99999);

        //act
        $response = $this->patchRequest($this->comic_endpoint.$comic_id, [
            'comic_vine_issue_id' => $comic_vine_issue_id
        ]);

        //assert
        $this->assertResponseStatus(404);

    }
    /**
     * @vcr comicvine-series.yml
     */
    public function test_it_cannot_query_meta_data_if_a_comic_vine_series_id_is_not_set_on_the_parent_series(){
        //arrange
        $comic = Factory::create('App\Comic', [
            'user_id' => $this->user->id,
            'series_id.user_id' => $this->user->id,
            'series_id.series_title' => 'All Star Superman',
            'series_id.comic_vine_series_id' => ''
        ]);
        //act
        $response = $this->getRequest($this->comic_endpoint.$comic->id."/meta");

        //assert
        $this->assertResponseStatus(400);

    }

}
