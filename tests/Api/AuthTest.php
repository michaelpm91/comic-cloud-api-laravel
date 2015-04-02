<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 22/03/15
 * Time: 22:00
 */

use App\User;
use Laracasts\TestDummy\Factory;


class AuthTest extends ApiTester {

    public function setUp(){
        parent::setUp();
        Artisan::call('db:seed');//TODO: Would be nice to move this...
    }

    public function test_it_generates_access_tokens(){
        //arrange
        $user = Factory::create('App\User', [
            'username' => 'auth_test_user',
            'password' => Hash::make('1234')
        ]);

        //act
        $response = $this->postRequest('/oauth/access_token', [
            'grant_type' => 'password',
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'username' => $user->email,
            'password' => '1234'
        ]);

        //assert
        $this->assertResponseOk();

    }

    public function test_it_can_register_user(){

        //arrange

        //act
        $response = $this->postRequest('/oauth/access_token', [
            'grant_type' => 'password',
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'username' => 'test@test.com',
            'password' => '1234'
        ]);

        //assert
        $this->assertResponseOk();

    }

}