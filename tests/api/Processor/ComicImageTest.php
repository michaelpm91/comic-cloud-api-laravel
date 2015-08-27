<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 15/08/15
 * Time: 11:09
 */

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Faker\Factory;

class ProcessorComicImageTest extends ApiTester {

    use DatabaseMigrations;


    /**
     * @group processor
     * @group image-test
     */
    public function test_it_must_be_authenticated(){
        $this->get($this->processor_comic_image_endpoint)->seeJson();
        $this->assertResponseStatus(401);
    }

    /**
     * @group processor
     * @group image-test
     */
    public function test_that_processor_clients_can_request_image_index(){
        $this->seed();

        $this->get($this->processor_comic_image_endpoint,['HTTP_Authorization' => 'Bearer '. $this->test_processor_access_token])->seeJson();

        $this->assertResponseStatus(200);

    }

    /**
     * @group processor
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

        $this->postJson($this->processor_comic_image_endpoint, $json, ['HTTP_Authorization' => 'Bearer '. $this->test_processor_access_token])->seeJson();
        $this->assertResponseStatus(201);


    }
}