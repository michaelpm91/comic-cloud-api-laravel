<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 15/08/15
 * Time: 11:09
 */

use Illuminate\Foundation\Testing\DatabaseMigrations;

use Faker\Factory;

class ComicImageTest extends TestCase{

    use DatabaseMigrations;

    protected $comic_image_endpoint = "/v0.1/images/";

    protected $test_basic_access_token = "y2ZRXZridqzVZP0mIzlaWBoQmLJplvqCcXmKOt4j";

    /**
     * @group image-test
     */
    public function test_it_must_be_authenticated(){
        $this->get($this->comic_image_endpoint.str_random(32))->seeJson();
        $this->assertResponseStatus(401);
    }
    public function test_admin_scoped_tokens_cannot_fetch_basic_scoped_images(){
        $this->markTestIncomplete('Incomplete');

    }
    public function test_processor_scoped_tokens_cannot_fetch_basic_scoped_images(){
        $this->markTestIncomplete('Incomplete');
    }


    /**
     * @group image-test
     */
    public function test_it_only_accepts_get_requests_to_a_specific_image(){
        $this->seed();

        $user = factory(App\Models\User::class)->create([
            'username' => 'auth_test_user',
            'password' => Hash::make('1234'),
            'type' => 'basic'
        ]);

        factory(App\Models\Comic::class)->create([
            'user_id' => $user->id
        ]);

        $img_slug = str_random(40);

        $this->post($this->comic_image_endpoint.$img_slug)->seeJson();

        $this->assertResponseStatus(405);

        $this->patch($this->comic_image_endpoint.$img_slug)->seeJson();

        $this->assertResponseStatus(405);

        $this->delete($this->comic_image_endpoint.$img_slug)->seeJson();

        $this->assertResponseStatus(405);
    }

    /**
     * @group image-test
     */
    public function test_that_basic_web_clients_cannot_send_requests_to_image_index(){
        $this->seed();

        $this->post($this->comic_image_endpoint, [], ['HTTP_Authorization' => 'Bearer ' . $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);

        $this->get($this->comic_image_endpoint,['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);

        $this->patch($this->comic_image_endpoint, [], ['HTTP_Authorization' => 'Bearer ' . $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);

        $this->delete($this->comic_image_endpoint,['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }

    /**
     * @vcr image-bucket.yml
     */
    public function test_it_fetches_images(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1
        ]);

        $contents = $comic->comic_book_archive_contents;
        $first_entry = head($contents);
        $req = $this->get($first_entry,['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token]);
        $this->assertResponseStatus(200);

        //dd($req);



    }



} 