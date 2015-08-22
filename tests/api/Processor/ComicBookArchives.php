<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Faker\Factory;

class ProcessorComicBookArchives extends TestCase{

    use DatabaseMigrations;

    protected $comic_book_archive_endpoint = "/processor/comicbookarchives/";

    protected $test_processor_access_token = "m7wQwuDdCq2FQvW2tjzALUnVc0KZe2YogLaxSOA6";

    /**
     * @group image-test
     */
    public function test_it_must_be_authenticated(){
        $this->get($this->comic_book_archive_endpoint.str_random(32))->seeJson();
        $this->assertResponseStatus(401);
    }
}