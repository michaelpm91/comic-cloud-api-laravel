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
    public function test_admin_scoped_tokens_cannot_fetch_basic_scoped_images(){
        $this->seed();

        $this->get($this->basic_comic_image_endpoint."xyz",['HTTP_Authorization' => 'Bearer '. $this->test_admin_access_token]);
        $this->assertResponseStatus(400);//TODO: this should be a 401
    }
    /**
     * @group admin
     * @group image-test
     */
    public function test_admin_scoped_tokens_cannot_fetch_processor_scoped_images(){
        $this->seed();

        $this->get($this->processor_comic_image_endpoint."xyz",['HTTP_Authorization' => 'Bearer '. $this->test_admin_access_token]);
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
     * @group lolz
     * @group admin
     * @group image-test
     */
    public function test_that_admins_can_delete_an_image(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1
        ]);

        $contents = $comic->comic_book_archive_contents;

        $slug = last(explode("/", head($contents)));

        $req = $this->delete($this->admin_comic_image_endpoint.$slug,['HTTP_Authorization' => 'Bearer '. $this->test_admin_access_token]);
        dd($req);

        $this->assertResponseStatus(200);

        $this->get($this->admin_comic_image_endpoint.$slug,['HTTP_Authorization' => 'Bearer '. $this->test_admin_access_token]);
        $this->assertResponseStatus(404);
    }
    /**
     * @group admin
     * @group image-test
     */
    public function test_that_admins_cannot_delete_images_that_do_not_exist(){
        $this->seed();

        $this->delete($this->admin_comic_image_endpoint."xyz",['HTTP_Authorization' => 'Bearer '. $this->test_admin_access_token]);
        $this->assertResponseStatus(404);
    }



}