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
    protected $test_basic_access_token = "y2ZRXZridqzVZP0mIzlaWBoQmLJplvqCcXmKOt4j";

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

        factory(App\Models\Comic::class)->create([
            'user_id' => $user->id
        ]);

        $img_slug = str_random(40);

        $this->post($this->comic_image_endpoint.$img_slug)->seeJson();

        $this->assertResponseStatus(405);

        $this->patch($this->comic_image_endpoint.$img_slug)->seeJson();

        $this->assertResponseStatus(405);

        $this->delete($this->comic_image_endpoint.$img_slug)->seeJson();

        $this->assertResponseStatus(405);


    }

    /**
     * @group image-test
     */
    public function test_that_basic_clients_cannot_send_requests_the_image_base_route(){
        $this->seed();

        $this->post($this->comic_image_endpoint, [],['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();

        $this->assertResponseStatus(400);

        $this->patch($this->comic_image_endpoint, [],['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();

        $this->assertResponseStatus(405);

        $this->delete($this->comic_image_endpoint, [],['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();

        $this->assertResponseStatus(405);

        $this->get($this->comic_image_endpoint, [],['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();

        $this->assertResponseStatus(405);

    }

} 