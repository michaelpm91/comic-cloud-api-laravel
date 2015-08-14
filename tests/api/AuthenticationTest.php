<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 14/08/15
 * Time: 19:59
 */
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthTest extends TestCase {

    use DatabaseMigrations;

    protected $oauth_endpoint = "/oauth/access_token/";
    protected $register_endpoint = "/auth/register/";

    public function setUp(){
        parent::setUp();
    }

    public function test_it_generates_access_tokens_via_the_password_grant_and_basic_scope(){
        $this->seed();

        $user = factory(App\Models\User::class)->make([
            'username' => 'auth_test_user',
            'password' => Hash::make('1234'),
            'type' => 'basic'
        ]);

        $response = $this->post($this->oauth_endpoint, [
            'grant_type' => 'password',
            'client_id' => 'SBziat92Is6qqShG',
            'client_secret' => 'dVPoCStWKNuAgsZagS21lqTKklpbVF8z',
            'username' => $user->username,
            'password' => '1234',
            'scope' => 'basic'
        ])->seeJson();

        //dd($response);

        $this->assertResponseOk();

    }

}