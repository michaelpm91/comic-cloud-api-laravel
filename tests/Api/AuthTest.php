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
        $this->assertJson($response);
        $this->assertResponseOk();

    }

    public function test_it_can_register_user(){

        //arrange

        //act
        $response = $this->postRequest('/auth/register', [
            'username' => 'test_2',
            'email' => 'test_2@test.com',
            'password' => '1234567',
            'password_confirmation' => '1234567'
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(201);

    }

    public function test_registering_a_user_will_fail_without_username(){

        //arrange

        //act
        $response = $this->postRequest('/auth/register', [
            'username' => '',
            'email' => 'test_2@test.com',
            'password' => '1234567',
            'password_confirmation' => '1234567'
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(400);

    }

    public function test_registering_a_user_will_fail_without_email(){

        //arrange

        //act
        $response = $this->postRequest('/auth/register', [
            'username' => 'test_2',
            'email' => '',
            'password' => '1234567',
            'password_confirmation' => '1234567'
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(400);

    }

    public function test_registering_a_user_will_fail_without_password(){

        //arrange

        //act
        $response = $this->postRequest('/auth/register', [
            'username' => 'test_2',
            'email' => 'test_2@test.com',
            'password' => '',
            'password_confirmation' => '1234567'
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(400);

    }

    public function test_registering_a_user_will_fail_without_password_confirmation(){

        //arrange

        //act
        $response = $this->postRequest('/auth/register', [
            'username' => 'test_2',
            'email' => 'test_2@test.com',
            'password' => '1234567',
            'password_confirmation' => ''
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(400);

    }

    public function test_registering_a_user_will_fail_if_passwords_do_not_match(){

        //arrange

        //act
        $response = $this->postRequest('/auth/register', [
            'username' => 'test_2',
            'email' => 'test_2@test.com',
            'password' => '12345678',
            'password_confirmation' => '87654321'
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(400);

    }

}