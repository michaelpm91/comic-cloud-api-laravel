<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 15/08/15
 * Time: 11:09
 */

use Illuminate\Foundation\Testing\DatabaseMigrations;

class ComicImageTest extends TestCase{

    use DatabaseMigrations;

    protected $comic_image_endpoint = "/v0.1/images/";

    /**
     * @group image-test
     */
    public function test_it_must_be_authenticated(){
        $this->get($this->comic_image_endpoint.str_random(32))->seeJson();

        $this->assertResponseStatus(401);
    }

    /**
     * @group image-test
     */
    public function test_it_only_accepts_get_requests_to_a_specific_image(){
        $this->seed();

        $user = factory(App\Models\User::class)->create([
            'username' => 'auth_test_user',
            'password' => Hash::make('1234'),
            'type' => 'basic'
        ]);
        $comic = factory(App\Models\Comic::class)->create();
        /*$comic = factory(App\Models\Comic::class)->create([
            'user_id' => $user->id
        ]);

        dd($comic);*/


    }

} 