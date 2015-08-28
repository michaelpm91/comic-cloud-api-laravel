<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 15/08/15
 * Time: 11:09
 */

use Illuminate\Foundation\Testing\DatabaseMigrations;

class ComicImageTest extends ApiTester{

    use DatabaseMigrations;

    /**
     * @group basic
     * @group image-test
     */
    public function test_it_must_be_authenticated(){
        $this->get($this->basic_comic_image_endpoint.str_random(32))->seeJson();
        $this->assertResponseStatus(401);
    }
    /**
     * @group basic
     * @group image-test
     */
    public function test_basic_scoped_tokens_cannot_fetch_admin_scoped_images(){
        $this->seed();

        $this->get($this->admin_comic_image_endpoint, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token]);
        $this->assertResponseStatus(400);//TODO: this should be a 401
    }
    /**
     * @group basic
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

        $this->post($this->basic_comic_image_endpoint.$img_slug)->seeJson();

        $this->assertResponseStatus(405);

        $this->patch($this->basic_comic_image_endpoint.$img_slug)->seeJson();

        $this->assertResponseStatus(405);

        $this->delete($this->basic_comic_image_endpoint.$img_slug)->seeJson();

        $this->assertResponseStatus(405);
    }

    /**
     * @group basic
     * @group image-test
     */
    public function test_that_basic_web_clients_cannot_send_requests_to_image_index(){
        $this->seed();

        $this->post($this->basic_comic_image_endpoint, [], ['HTTP_Authorization' => 'Bearer ' . $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);

        $this->get($this->basic_comic_image_endpoint,['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);

        $this->patch($this->basic_comic_image_endpoint, [], ['HTTP_Authorization' => 'Bearer ' . $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);

        $this->delete($this->basic_comic_image_endpoint,['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }
    /**
     * @group basic
     * @group image-test
     */
    public function test_it_fetches_images(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1
        ]);

        $contents = $comic->comic_book_archive_contents;

        $this->get(head($contents),['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token]);
        $this->assertResponseStatus(200);
    }
    /**
     * @group basic
     * @group image-test
     */
    public function test_it_cannot_fetch_images_that_do_not_exist(){
        $this->seed();

        $this->get($this->basic_comic_image_endpoint."xyz",['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token]);
        $this->assertResponseStatus(404);
    }
    /**
     * @group basic
     * @group image-test
     */
    public function test_it_can_only_fetch_images_that_belong_to_the_user(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create();

        $contents = $comic->comic_book_archive_contents;

        $this->get(head($contents),['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token]);
        $this->assertResponseStatus(404);
    }
} 