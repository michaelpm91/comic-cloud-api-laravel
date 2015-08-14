<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 14/08/15
 * Time: 19:59
 */
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthenticationTest extends TestCase {

    use DatabaseMigrations;

    protected $oauth_endpoint = "/oauth/access_token/";
    protected $register_endpoint = "/auth/register/";

    protected $web_client_id = 'SBziat92Is6qqShG';
    protected $web_client_secret = 'dVPoCStWKNuAgsZagS21lqTKklpbVF8z';

    protected $admin_web_client_id = 'PJQG0e3tOKWibQAS';
    protected $admin_web_client_secret = 'WDOMm55MIsz4DoExTEnpyuZ1Nq6khZLN';

    protected $lambda_processor_client_id = 'r9kO96j16pDdmQf9';
    protected $lambda_processor_client_secret = 'jeeSHlMdKO1wHhVtGzCmUwMaH0CbzJRy';


    /**
     * @group auth-test
     */
    public function test_it_generates_access_tokens_via_the_password_grant_and_basic_scope(){
        $this->seed();

        $user = factory(App\Models\User::class)->create([
            'username' => 'auth_test_user',
            'password' => Hash::make('1234'),
            'type' => 'basic'

        ]);

        $this->post($this->oauth_endpoint, [
            'grant_type' => 'password',
            'client_id' => $this->web_client_id,
            'client_secret' => $this->web_client_secret,
            'username' => $user->username,
            'password' => '1234',
            'scope' => 'basic'
        ])->seeJson();

        $this->assertResponseOk();
    }

    /**
     * @group auth-test
     */
    public function test_it_generates_access_tokens_via_the_password_admin_grant_and_admin_scope(){
        $this->seed();

        $user = factory(App\Models\User::class)->create([
            'username' => 'auth_test_user',
            'password' => Hash::make('1234'),
            'type' => 'admin'

        ]);

        $this->post($this->oauth_endpoint, [
            'grant_type' => 'password_admin',
            'client_id' => $this->admin_web_client_id,
            'client_secret' => $this->admin_web_client_secret,
            'username' => $user->username,
            'password' => '1234',
            'scope' => 'admin'
        ])->seeJson();

        $this->assertResponseOk();
    }

    /**
     * @group auth-test
     */
    public function test_it_generates_access_tokens_via_the_processor_grant_and_processor_scope(){
        $this->seed();

        $this->post($this->oauth_endpoint, [
            'grant_type' => 'client_credentials',
            'client_id' => $this->lambda_processor_client_id,
            'client_secret' => $this->lambda_processor_client_secret,
            'scope' => 'processor'
        ])->seeJson();

        $this->assertResponseOk();
    }

    /**
     * @group auth-test
     */
    public function test_it_cannot_generate_access_tokens_if_the_grant_type_is_not_available_in_the_scope(){
        $this->seed();

        $this->post($this->oauth_endpoint, [
            'grant_type' => 'client_credentials',
            'client_id' => $this->web_client_id,
            'client_secret' => $this->web_client_secret,

        ])->seeJson();

        $this->assertResponseStatus(401);
    }

    /* PORT BELOW */

    public function test_it_cannot_generate_access_tokens_if_the_client_does_not_have_access_to_the_scope(){
        $this->seed();

        $user = factory(App\Models\User::class)->create([
            'username' => 'auth_test_user',
            'password' => Hash::make('1234'),
            'type' => 'admin'
        ]);

        $this->post($this->oauth_endpoint, [
            'grant_type' => 'password',
            'client_id' => $this->web_client_id,
            'client_secret' => $this->web_client_secret,
            'username' => $user->username,
            'password' => '1234',
            'scope' => 'admin'
        ])->seeJson();

        $this->assertResponseStatus(401);
    }
    /**
     * @group auth-test
     */
    public function test_it_cannot_generate_access_tokens_if_the_scope_is_not_available_to_the_grant_type(){
        $this->seed();

        $user = factory(App\Models\User::class)->create([
            'username' => 'auth_test_user',
            'password' => Hash::make('1234'),
            'type' => 'basic'
        ]);

        
        $this->post($this->oauth_endpoint, [
            'grant_type' => 'password',
            'client_id' => $this->web_client_id,
            'client_secret' => $this->web_client_secret,
            'username' => $user->username,
            'password' => '1234',
            'scope' => 'processor'
        ])->seeJson();

        $this->assertResponseStatus(400);
    }
    /**
     * @group auth-test
     */
    public function test_it_can_register_user(){
        $this->post($this->register_endpoint, [
            'username' => 'test_2',
            'email' => 'test_2@test.com',
            'password' => '1234567',
            'password_confirmation' => '1234567'
        ])->seeJson();

        $this->assertResponseStatus(201);
    }
    /**
     * @group auth-test
     */
    public function test_registering_a_user_will_fail_without_username(){
        $this->post($this->register_endpoint, [
            'username' => '',
            'email' => 'test_2@test.com',
            'password' => '1234567',
            'password_confirmation' => '1234567'
        ])->seeJson();

        $this->assertResponseStatus(400);
    }
    /**
     * @group auth-test
     */
    public function test_registering_a_user_will_fail_without_email(){
        $this->post($this->register_endpoint, [
            'username' => 'test_2',
            'email' => '',
            'password' => '1234567',
            'password_confirmation' => '1234567'
        ])->seeJson();
        
        $this->assertResponseStatus(400);
    }
    /**
     * @group auth-test
     */
    public function test_registering_a_user_will_fail_without_password(){
        $this->post($this->register_endpoint, [
            'username' => 'test_2',
            'email' => 'test_2@test.com',
            'password' => '',
            'password_confirmation' => '1234567'
        ])->seeJson();
        
        $this->assertResponseStatus(400);
    }
    /**
     * @group auth-test
     */
    public function test_registering_a_user_will_fail_without_password_confirmation(){
        $this->post($this->register_endpoint, [
            'username' => 'test_2',
            'email' => 'test_2@test.com',
            'password' => '1234567',
            'password_confirmation' => ''
        ])->seeJson();

        $this->assertResponseStatus(400);
    }
    /**
     * @group auth-test
     */
    public function test_registering_a_user_will_fail_if_passwords_do_not_match(){
        $this->post($this->register_endpoint, [
            'username' => 'test_2',
            'email' => 'test_2@test.com',
            'password' => '12345678',
            'password_confirmation' => '87654321'
        ])->seeJson();
        
        $this->assertResponseStatus(400);
    }

}