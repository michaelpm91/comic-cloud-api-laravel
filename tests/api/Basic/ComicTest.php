<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 28/08/15
 * Time: 20:22
 */

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Pagination\LengthAwarePaginator;

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

    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_cannot_fetch_a_comic_that_does_not_exist(){
        $this->seed();
        $this->get($this->basic_comic_endpoint."xyz",['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_that_comic_index_only_returns_comics_that_belong_to_the_user(){
        $this->seed();
        $other_user_comics = factory(App\Models\Comic::class, 5)->create();
        $user_comics = factory(App\Models\Comic::class, 5)->create(['user_id' => 1]);
        $user_comics_array = $user_comics->toArray();


        $this->get($this->basic_comic_endpoint, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])
            ->seeJsonEquals([
                'comic' => $user_comics_array,
                'current_page' => 1,
                "from" => 1,
                "last_page" => 1,
                "next_page_url" => null,
                "per_page" => env('paginate_per_page'),
                "prev_page_url" => null,
                "to" => 5,
                "total" => 5
            ]);
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_a_user_cannot_fetch_a_comic_that_belongs_to_another_user(){
        $this->seed();
        $comic = factory(App\Models\Comic::class)->create();
        $this->get($this->basic_comic_endpoint.$comic->id, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }

    /**
     * @group basic
     * @group comic-test
     */
    public function test_a_user_can_edit_a_comic(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1
        ]);

        $series = factory(App\Models\Series::class)->create([
            'user_id' => 1
        ]);

        $this->put($this->basic_comic_endpoint.$comic->id, [
            'comic_writer' => 'John Smith',
            'comic_issue' => 1,
            'comic_vine_issue_id' => 123123,
            'series_id' => $series->id
        ], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson([
            'comic_writer' => 'John Smith',
            'comic_issue' => 1,
            'comic_vine_issue_id' => 123123,
            'series_id' => $series->id
        ]);
        $this->assertResponseOk();
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_a_user_cannot_set_a_series_id_that_does_not_exist(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1
        ]);

        $this->put($this->basic_comic_endpoint.$comic->id, [
            'series_id' => 'xyz'
        ], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(400);
    }

    /**
     * @group basic
     * @group comic-test
     */
    public function test_a_user_cannot_set_a_series_id_that_does_not_belong_to_the_user(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1
        ]);

        $series = factory(App\Models\Series::class)->create();

        $this->put($this->basic_comic_endpoint.$comic->id, [
            'series_id' => $series->id
        ], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(400);

    }

    /**
     * @group basic
     * @group comic-test
     */
    public function test_a_user_cannot_edit_a_comic_that_does_not_belong_to_the_user(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create();

        $this->put($this->basic_comic_endpoint.$comic->id, [
            'comic_writer' => 'John Smith',
            'comic_issue' => 1,
            'comic_vine_issue_id' => 123123
        ], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }

    /**
     * @group basic
     * @group comic-test
     */
    public function test_a_user_cannot_edit_a_comic_that_does_not_exist(){
        $this->seed();

        $this->put($this->basic_comic_endpoint."xyz", [
            'comic_writer' => 'John Smith',
            'comic_issue' => 1,
            'comic_vine_issue_id' => 123123
        ], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }

    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_returns_an_appropriate_message_when_no_data_is_sent(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1
        ]);
        $this->put($this->basic_comic_endpoint.$comic->id, [], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])
            ->seeJson(["detail" => "No Data Sent"]);
        $this->assertResponseStatus(400);
    }

    /**
     * @group basic
     * @group comic-test
     */
    public function test_a_user_can_delete_a_comic(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1
        ]);

        $this->delete($this->basic_comic_endpoint.$comic->id, [], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseOk();
        $this->get($this->basic_comic_endpoint.$comic->id, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_a_user_cannot_delete_a_comic_of_another_user(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create();

        $this->delete($this->basic_comic_endpoint.$comic->id, [], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }

    /**
     * @group basic
     * @group comic-test
     */
    public function test_a_user_cannot_delete_a_comic_that_does_not_exist(){
        $this->seed();

        $this->delete($this->basic_comic_endpoint."xyz", [], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }

    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_will_delete_a_series_if_the_last_comic_has_been_deleted(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 1])->id
        ]);
        $series_id = $comic->series->id;

        $this->get($this->basic_series_endpoint.$series_id, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseOk();
        $this->delete($this->basic_comic_endpoint.$comic->id, [], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseOk();
        $this->get($this->basic_series_endpoint.$series_id, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_can_fetch_meta_data_for_a_comic_that_exists(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create([
                'user_id' => 1,
                'series_title' => 'All Star Superman',
                'comic_vine_series_id' => '18139'
            ])->id
        ]);
        $this->get($this->basic_comic_endpoint.$comic->id."/meta", ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token]);
        $this->assertResponseOk();
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_cannot_fetch_meta_data_for_a_comic_that_does_not_exist(){
        $this->seed();

        $this->get($this->basic_comic_endpoint."xyz/meta", ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_cannot_query_meta_data_if_a_comic_vine_series_id_is_not_set_on_the_parent_series(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create([
                'user_id' => 1,
                'series_title' => 'All Star Superman',
                'comic_vine_series_id' => ''
            ])->id
        ]);

        $this->get($this->basic_comic_endpoint.$comic->id."/meta", ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(400);
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_will_fail_gracefully_if_comic_vine_is_unavailable(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create([
                'user_id' => 1,
                'series_title' => 'All Star Superman',
                'comic_vine_series_id' => '18139'
            ])->id
        ]);
        $this->get($this->basic_comic_endpoint.$comic->id."/meta?force=comic_vine_error", ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(500);
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_will_return_the_last_page_when_requesting_outside_the_page_count(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create([
                'user_id' => 1,
                'series_title' => 'All Star Superman',
                'comic_vine_series_id' => '18139'
            ])->id
        ]);
        $req = $this->get($this->basic_comic_endpoint.$comic->id."/meta", ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(200);

        $this->get($this->basic_comic_endpoint.$comic->id."/meta?page=9001", ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])
            ->seeJsonEquals(json_decode($req->response->getContent(), true));
        $this->assertResponseStatus(200);
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_fetch_a_related_series_for_a_specific_comic(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 1])->id
        ]);

        $this->get($this->basic_comic_endpoint.$comic->id."/series",['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])
            ->seeJson(['id' => $comic->series->id]);
        $this->assertResponseStatus(200);
    }
    /**
     * @group basic
     * @group comic-test
     */
    public function test_it_cannot_fetch_a_related_series_for_a_comic_that_does_not_exist(){
        $this->seed();
        $this->get($this->basic_comic_endpoint."xyz/series",['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }
}
