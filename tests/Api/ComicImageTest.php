<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 04/04/15
 * Time: 00:08
 */

use App\User;

class ComicImageTest extends ApiTester {

    protected $user;
    protected $auth_header;

    public function setUp(){//runs per test :(
        parent::setUp();
        Artisan::call('db:seed');//TODO: Would be nice to move this...
        $this->user = User::find(1);
    }
    public function test_must_be_authenticated(){
        //arrange
        $this->test_access_token = "";

        //act
        $response = $this->getRequest('/image/xxxxx');

        //assert
        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are madem ore consistent

    }

    public function test_it_fetches_image(){//Retrieve single image
        //arrange
        /*$comic = Factory::create('App\Comic', ['user_id' => $this->user->id]);

        //act
        $response = $this->getRequest('/comic/'.$comic->id);

        //assert
        $this->assertResponseOk();*/

    }
    public function test_it_cannot_fetch_an_image_that_does_not_exist(){
        //arrange

        //act
        /*$response = $this->getRequest('/comic/xxxxx');

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent
        */
    }
    public function test_it_fetches_user_comic_image_only(){//
        //arrange
        /*$user_comics = Factory::times(5)->create('App\Comic', ['user_id' => $this->user->id]);
        $other_user_comic = Factory::create('App\Comic', ['user_id' => 2]);

        //act
        $response = $this->getRequest('/comic');

        //assert
        $result = false;
        foreach(json_decode($response, true)['Comics'] as $comic){
            if($comic['id'] == $other_user_comic->id) $result = true;
        }
        $this->assertEquals(false, $result);*/

    }

}
 