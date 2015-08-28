<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 28/08/15
 * Time: 20:22
 */

use Illuminate\Foundation\Testing\DatabaseMigrations;

class ComicTest extends ApiTester{

    use DatabaseMigrations;

    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_must_be_authenticated(){
        $this->get($this->basic_comic_endpoint.str_random(32))->seeJson();
        $this->assertResponseStatus(401);
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_does_not_accept_post_requests(){
        $this->post($this->basic_comic_endpoint)->seeJson();
        $this->assertResponseStatus(405);
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_fetches_all_comics(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1
        ]);

        $this->get($this->basic_comic_endpoint,['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])
            ->seeJson(['id' => $comic->id]);
        $this->assertResponseStatus(200);
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_fetches_a_specific_comic(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1
        ]);

        $this->get($this->basic_comic_endpoint.$comic->id,['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])
            ->seeJson(['id' => $comic->id]);
        $this->assertResponseStatus(200);
    }
}
