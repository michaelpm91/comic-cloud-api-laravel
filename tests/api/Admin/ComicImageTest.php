<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 15/08/15
 * Time: 11:09
 */

use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminComicImageTest extends ApiTester {

    use DatabaseMigrations;

    /**
     * @group admin
     * @group image-test
     */
    public function test_it_must_be_authenticated(){
        $this->get($this->admin_comic_image_endpoint.str_random(32))->seeJson();
        $this->assertResponseStatus(401);
    }
    /**
     * @group admin
     * @group image-test
     */
    public function test_admin_scoped_tokens_cannot_fetch_basic_scoped_images()
    {
        $this->seed();

        $this->get($this->basic_comic_image_endpoint . "xyz", ['HTTP_Authorization' => 'Bearer ' . $this->test_admin_access_token]);
        $this->assertResponseStatus(400);//TODO: this should be a 401
    }
    /**
     * @group admin
     * @group image-test
     */
    public function test_admin_scoped_tokens_cannot_reach_processor_index_for_images(){
        $this->seed();

        $this->get($this->processor_comic_image_endpoint,['HTTP_Authorization' => 'Bearer '. $this->test_admin_access_token]);
        $this->assertResponseStatus(400);//TODO: this should be a 401
    }
    /**
     * @group admin
     * @group image-test
     */
    public function test_that_admins_cannot_send_post_requests_to_image_index(){
        $this->seed();

        $this->post($this->admin_comic_image_endpoint, [], ['HTTP_Authorization' => 'Bearer ' . $this->test_admin_access_token])->seeJson();
        $this->assertResponseStatus(405);
    }
    /**
     * @group admin
     * @group image-test
     */
    public function test_that_admins_get_specific_images(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1
        ]);

        $contents = $comic->comic_book_archive_contents;

        $slug = last(explode("/", head($contents)));

        $this->get($this->admin_comic_image_endpoint.$slug, ['HTTP_Authorization' => 'Bearer '. $this->test_admin_access_token]);
        $this->assertResponseStatus(200);
    }
    /**
     * @group admin
     * @group image-test
     */
    public function test_that_admins_get_all_images(){
        $this->seed();

        $this->get($this->admin_comic_image_endpoint, ['HTTP_Authorization' => 'Bearer '. $this->test_admin_access_token]);
        $this->assertResponseStatus(200);
    }
    /**
     * @group admin
     * @group image-test
     */
    public function test_that_admins_cannot_get_images_that_do_not_exist(){
        $this->seed();

        $this->get($this->admin_comic_image_endpoint."xyz",['HTTP_Authorization' => 'Bearer '. $this->test_admin_access_token]);
        $this->assertResponseStatus(404);
    }
    /**
     * @group admin
     * @group image-test
     */
    public function test_that_admins_can_update_an_image(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create();

        $contents = $comic->comic_book_archive_contents;

        $faker = Faker\Factory::create();

        $request_body = [
            'image_size' => $faker->numberBetween(1000000, 50000000),
            'image_url' => $faker->imageUrl(600, 960, 'abstract'),
            'image_hash' => $faker->md5
        ];

        $slug = last(explode("/", head($contents)));

        $this->put($this->admin_comic_image_endpoint.$slug, $request_body,['HTTP_Authorization' => 'Bearer '. $this->test_admin_access_token])
            ->seeJson([
                'image_size' => $request_body['image_size'],
                'image_url' => $request_body['image_url'],
                'image_hash' => $request_body['image_hash']
            ]);
        $this->assertResponseStatus(200);
    }
    /**
     * @group admin
     * @group image-test
     */
    public function test_that_an_update_to_an_image_with_no_body_will_fail(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create();

        $contents = $comic->comic_book_archive_contents;

        $slug = last(explode("/", head($contents)));

        $this->put($this->admin_comic_image_endpoint.$slug, [],['HTTP_Authorization' => 'Bearer '. $this->test_admin_access_token])->seeJson();
        $this->assertResponseStatus(400);
    }
    /**
     * @group admin
     * @group image-test
     */
    public function test_that_an_update_must_contain_valid_parameter()
    {
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create();

        $contents = $comic->comic_book_archive_contents;

        $request_body = [
            'image_size' => 'q',
            'image_url' => 'xyz',
            'image_hash' => 'zyx'
        ];

        $slug = last(explode("/", head($contents)));

        $this->put($this->admin_comic_image_endpoint . $slug, $request_body, ['HTTP_Authorization' => 'Bearer ' . $this->test_admin_access_token])->seeJson();

        $this->assertResponseStatus(400);
    }
}