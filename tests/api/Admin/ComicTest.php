<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 28/08/15
 * Time: 20:22
 */

use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminComicTest extends ApiTester{

    use DatabaseMigrations;

    /**
     * @group admin
     * @group comic-test
     */
    public function test_it_must_be_authenticated(){
        $this->get($this->admin_comic_endpoint.str_random(32))->seeJson();
        $this->assertResponseStatus(401);
    }

}
