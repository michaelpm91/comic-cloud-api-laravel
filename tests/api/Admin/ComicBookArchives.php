<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Faker\Factory;

class AdminComicBookArchives extends TestCase{

    use DatabaseMigrations;

    protected $comic_book_archive_endpoint = "/admin/images/";

    protected $test_admin_access_token = "iw8yKb073hI0O8szPou8ZliIlvzLHS9sPrT4WmmJ";

    /**
     * @group image-test
     */
    public function test_it_must_be_authenticated(){
        $this->get($this->comic_book_archive_endpoint.str_random(32))->seeJson();
        $this->assertResponseStatus(401);
    }
}