<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 15/08/15
 * Time: 11:09
 */

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Faker\Factory;

class AdminComicImageTest extends TestCase{

    use DatabaseMigrations;

    protected $comic_image_endpoint = "/admin/images/";

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
    public function test_that_admin_web_clients_cannot_send_post_requests_to_image_index(){
        $this->seed();

        $this->post($this->comic_image_endpoint, [], ['HTTP_Authorization' => 'Bearer ' . $this->test_admin_access_token])->seeJson();
        $this->assertResponseStatus(405);

    }

}