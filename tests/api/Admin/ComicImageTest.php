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
    public function test_that_admin_web_clients_cannot_send_post_requests_to_image_index(){
        $this->seed();

        $this->post($this->admin_comic_image_endpoint, [], ['HTTP_Authorization' => 'Bearer ' . $this->test_admin_access_token])->seeJson();
        $this->assertResponseStatus(405);

    }

}