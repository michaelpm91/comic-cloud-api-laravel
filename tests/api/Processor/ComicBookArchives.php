<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProcessorComicBookArchives extends ApiTester {

    use DatabaseMigrations;

    /**
     * @group processor
     * @group image-test
     */
    public function test_it_must_be_authenticated(){
        $this->get($this->processor_comic_book_archive_endpoint.str_random(32))->seeJson();
        $this->assertResponseStatus(401);
    }
}