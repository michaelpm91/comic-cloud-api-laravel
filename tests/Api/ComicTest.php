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
        Auth::loginUsingId($this->user->id);

    }
    public function test_must_be_authenticated(){
        //arrange
        $this->test_access_token = "";

        //act
        $response = $this->getRequest('/comic');

        //assert
        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are madem ore consistent

    }
    //READ
    public function test_it_fetches_comics(){//Retrieve all user comics
        //arrange
        $comics = Factory::times(10)->create('App\Comic', ['user_id' => $this->user->id]);

        //act
        $response = $this->getRequest('/comic');
        dd($response);

        //assert
        $this->assertResponseOk();
    }
    public function test_it_fetches_comic(){//Retrieve single comic
        //arrange
        $comic = Factory::create('App\Comic', ['user_id' => $this->user->id]);

        //act
        $response = $this->getRequest('/comic/'.$comic->id);

        //assert
        $this->assertResponseOk();

    }
    public function test_it_fetches_user_comics_only(){//
        //arrange
        $user_comics = Factory::times(5)->create('App\Comic', ['user_id' => $this->user->id]);
        $other_user_comic = Factory::create('App\Comic', ['user_id' => 2]);

        //act
        $response = $this->getRequest('/comic');

        //assert
        $result = false;
        foreach(json_decode($response, true)['Comics'] as $comic){
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
}
