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


class ComicTest extends ApiTester {

    protected $user;
    protected $auth_header;

    public function setUp(){//runs per test :(
        parent::setUp();
        Artisan::call('db:seed');//TODO: Would be nice to move this...
        $this->user = User::find(1);
    }
    public function test_it_must_be_authenticated(){
        //arrange
        $this->test_access_token = "";

        //act
        $response = $this->getRequest('/comic');

        //assert
        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are madem ore consistent

    }
    public function test_it_fetches_all_comics(){//Retrieve all user comics
        //arrange
        $mocked_comics = Factory::times(10)->create('App\Comic', ['user_id' => $this->user->id]);

        //act
        $response = $this->getRequest('/comic');

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
        $response = $this->getRequest('/comic/'.$comic->id);

        //assert
        $this->assertResponseOk();

    }
    public function test_it_cannot_fetch_a_comic_that_does_not_exist(){
        //arrange

        //act
        $response = $this->getRequest('/comic/'.str_random(40));

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_fetches_user_comics_only(){//
        //arrange
        $user_comics = Factory::times(5)->create('App\Comic', ['user_id' => $this->user->id]);
        $other_user_comic = Factory::create('App\Comic', ['user_id' => 2]);

        //act
        $response = $this->getRequest('/comic');

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
        $response = $this->getRequest('/comic/'.$comic->id);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_can_edit_a_comic_comic_writer(){//TODO:Multiple Asserts...
        //arrange
        $comic = Factory::create('App\Comic', ['user_id' => $this->user->id]);

        //act
        $response = $this->patchRequest('/comic/'.$comic->id, [
            'comic_writer' => 'John Smith'
        ]);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest('/comic/'.$comic->id);

        //assert
        $this->assertEquals('John Smith', json_decode($response, true)['comic']['comic_writer']);


    }
    public function test_it_can_edit_a_comic_comic_issue(){//TODO:Multiple Asserts
        //arrange
        $comic = Factory::create('App\Comic', ['user_id' => $this->user->id]);

        //act
        $response = $this->patchRequest('/comic/'.$comic->id, [
            'comic_issue' => 1
        ]);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest('/comic/'.$comic->id);

        //assert
        $this->assertEquals(1, json_decode($response, true)['comic']['comic_issue']);
    }
    public function test_it_can_set_a_comic_series_id_to_one_that_exists(){

    }
    public function test_it_cannot_set_a_comic_series_id_to_one_that_does_not_exist(){

    }
    public function test_it_cannot_edit_another_users_comic(){

        //arrange
        $otherusercomic = Factory::create('App\Comic', ['user_id' => 2]);

        //act
        $response = $this->patchRequest('/comic/'.$otherusercomic->id, [
            'comic_issue' => 1
        ]);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_cannot_edit_a_comic_comic_issue_that_does_not_exist(){
        //arrange

        //act
        $response = $this->patchRequest('/comic/'.str_random(40), [
            'comic_issue' => 1
        ]);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_cannot_edit_a_comic_comic_writer_that_does_not_exist(){
        //arrange

        //act
        $response = $this->patchRequest('/comic/'.str_random(40), [
            'comic_writer' => 'John Smith'
        ]);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_returns_an_appropriate_message_when_no_edit_fields_are_entered(){

    }
    public function test_it_can_delete_a_comic(){//TODO: Multiple asserts
        //arrange
        $comic = Factory::create('App\Comic', ['user_id' => $this->user->id]);

        //act
        $response = $this->deleteRequest('/comic/'.$comic->id);

        //assert
        $this->assertResponseOk();

        //act
        $response = $this->getRequest('/comic/'.$comic->id);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_cannot_delete_a_comic_that_does_not_exist(){
        //arrange

        //act
        $response = $this->deleteRequest('/comic/'.str_random(40));

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent


    }
    public function test_it_cannot_delete_another_users_comic(){
        //arrange
        $otherusercomic = Factory::create('App\Comic', ['user_id' => 2]);

        //act
        $response = $this->deleteRequest('/comic/'.$otherusercomic->id);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_will_delete_a_series_if_the_last_comic_has_been_deleted(){

    }
}
