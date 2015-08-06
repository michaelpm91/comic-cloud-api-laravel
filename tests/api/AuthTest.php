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

    protected $oauth_endpoint = "/v0.1/oauth/access_token/";
    protected $register_endpoint = "/v0.1/auth/register/";

    public function setUp(){
        parent::setUp();
        Artisan::call('db:seed');//TODO: Would be nice to move this...
    }
    /**
     * @group auth-test
     */
    public function test_it_generates_access_tokens_via_the_password_grant_and_basic_scope(){
        //arrange
        $user = Factory::create('App\User', [
            'username' => 'auth_test_user',
            'password' => Hash::make('1234')
        ]);

        //act
        $response = $this->postRequest($this->oauth_endpoint, [
            'grant_type' => 'password',
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'username' => $user->username,
            'password' => '1234',
            'scope' => 'basic'
        ]);

        //assert
        $this->assertJson($response);
        $this->assertResponseOk();

    }
    /**
     * @group auth-test
     */
    public function test_it_generates_access_tokens_via_the_password_grant_and_admin_scope(){
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    /**
     * @group auth-test
     */
    public function test_it_cannot_generate_access_tokens_if_the_grant_type_is_not_available_in_the_scope(){
        //arrange
        $user = Factory::create('App\User', [
            'username' => 'auth_test_user',
            'password' => Hash::make('1234')
        ]);

        //act
        $response = $this->postRequest($this->oauth_endpoint, [
            'grant_type' => 'client_credentials',
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'username' => $user->username,
            'password' => '1234',
            'scope' => 'basic'
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(401);

    }
    public function test_it_cannot_generate_access_tokens_if_the_client_does_not_have_access_to_the_scope(){
        //arrange
        $user = Factory::create('App\User', [
            'username' => 'auth_test_user',
            'password' => Hash::make('1234')
        ]);

        //act
        $response = $this->postRequest($this->oauth_endpoint, [
            'grant_type' => 'password',
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'username' => $user->username,
            'password' => '1234',
            'scope' => 'admin'
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(401);

    }
    /**
     * @group auth-test
     */
    public function test_it_cannot_generate_access_tokens_if_the_scope_is_not_available_to_the_grant_type(){
        //arrange
        $user = Factory::create('App\User', [
            'username' => 'auth_test_user',
            'password' => Hash::make('1234')
        ]);

        //act
        $response = $this->postRequest($this->oauth_endpoint, [
            'grant_type' => 'password',
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'username' => $user->username,
            'password' => '1234',
            'scope' => 'processor'
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(400);

    }
    /**
     * @group auth-test
     */
    public function test_it_can_register_user(){

        //arrange

        //act
        $response = $this->postRequest($this->register_endpoint, [
            'username' => 'test_2',
            'email' => 'test_2@test.com',
            'password' => '1234567',
            'password_confirmation' => '1234567'
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(201);

    }
    /**
     * @group auth-test
     */
    public function test_registering_a_user_will_fail_without_username(){

        //arrange

        //act
        $response = $this->postRequest($this->register_endpoint, [
            'username' => '',
            'email' => 'test_2@test.com',
            'password' => '1234567',
            'password_confirmation' => '1234567'
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(400);

    }
    /**
     * @group auth-test
     */
    public function test_registering_a_user_will_fail_without_email(){

        //arrange

        //act
        $response = $this->postRequest($this->register_endpoint, [
            'username' => 'test_2',
            'email' => '',
            'password' => '1234567',
            'password_confirmation' => '1234567'
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(400);

    }
    /**
     * @group auth-test
     */
    public function test_registering_a_user_will_fail_without_password(){

        //arrange

        //act
        $response = $this->postRequest($this->register_endpoint, [
            'username' => 'test_2',
            'email' => 'test_2@test.com',
            'password' => '',
            'password_confirmation' => '1234567'
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(400);

    }
    /**
     * @group auth-test
     */
    public function test_registering_a_user_will_fail_without_password_confirmation(){

        //arrange

        //act
        $response = $this->postRequest($this->register_endpoint, [
            'username' => 'test_2',
            'email' => 'test_2@test.com',
            'password' => '1234567',
            'password_confirmation' => ''
        ]);
        //assert
        $this->assertJson($response);
        $this->assertResponseStatus(400);

    }
    /**
     * @group auth-test
     */
    public function test_registering_a_user_will_fail_if_passwords_do_not_match(){

        //arrange

        //act
        $response = $this->postRequest($this->register_endpoint, [
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