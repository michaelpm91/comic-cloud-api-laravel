<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 15/08/15
 * Time: 11:09
 */

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Faker\Factory;

class ProcessorComicImageTest extends TestCase{

    use DatabaseMigrations;

    protected $comic_image_endpoint = "/processor/images/";

    protected $test_processor_access_token = "m7wQwuDdCq2FQvW2tjzALUnVc0KZe2YogLaxSOA6";

    /**
     * @group image-test
     */
    public function test_it_must_be_authenticated(){
        //$this->markTestIncomplete('Prcoessor clients cannot ');
        $this->get($this->comic_image_endpoint)->seeJson();
        $this->assertResponseStatus(401);
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