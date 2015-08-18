<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 15/08/15
 * Time: 11:09
 */

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Faker\Factory;

class ComicImageTest extends TestCase{

    use DatabaseMigrations;

    protected $comic_image_endpoint = "/v0.1/images/";

    protected $test_basic_access_token = "y2ZRXZridqzVZP0mIzlaWBoQmLJplvqCcXmKOt4j";
    protected $test_processor_access_token = "m7wQwuDdCq2FQvW2tjzALUnVc0KZe2YogLaxSOA6";
    protected $test_admin_access_token = "iw8yKb073hI0O8szPou8ZliIlvzLHS9sPrT4WmmJ";

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
    public function test_that_basic_web_clients_cannot_send_requests_to_image_index(){
        $this->seed();

        $this->post($this->comic_image_endpoint, [], ['HTTP_Authorization' => 'Bearer ' . $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(400);

        $this->get($this->comic_image_endpoint,['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(400);//TODO: This should eventually be 401
    }

    /**
     * @group image-test
     */
    public function test_that_admin_web_clients_cannot_send_requests_to_image_index(){
        $this->seed();

        $this->post($this->comic_image_endpoint, [], ['HTTP_Authorization' => 'Bearer ' . $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(400);

        $this->get($this->comic_image_endpoint,['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(400);
    }

    /**
     * @group image-test
     */
    public function test_that_processor_clients_can_request_image_index(){
        $this->seed();

        $this->get($this->comic_image_endpoint,['HTTP_Authorization' => 'Bearer '. $this->test_processor_access_token])->seeJson();

        $this->assertResponseStatus(200);

    }

    /**
     * @group image-test
     */
    public function test_that_processor_clients_can_create_image_records(){
        $this->seed();

        $cba = factory(App\Models\ComicBookArchive::class)->create();

        $faker = Factory::create();
        $thingy = $faker->uuid;
        $json = [
            "image_slug" => $thingy,
            "image_hash" => $faker->md5,
            "image_url" =>  $faker->imageUrl(600, 960, 'cats'),
            "image_size" => $faker->numberBetween(1000000, 50000000),
            "related_comic_book_archive_id" => $cba->id
        ];

        $headers = [
            'HTTP_Authorization' => 'Bearer ' . $this->test_processor_access_token,
            'HTTP_CONTENT_TYPE' => 'application/json'
        ];
        $this->call('POST', $this->comic_image_endpoint, [], [], [], $headers,json_encode($json));//TODO: Replace with working postJson()
        $this->assertJson($this->response->getContent());//TODO: chain onto postJson with seeJson()

        $this->assertResponseStatus(201);


    }

} 