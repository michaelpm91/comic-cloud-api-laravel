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
        Auth::logout();
        $this->test_access_token = "";

        //act
        $response = $this->getRequest('/comic');

        //assert
        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are madem ore consistent

    }
}
