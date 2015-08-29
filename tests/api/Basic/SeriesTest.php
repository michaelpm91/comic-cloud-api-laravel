<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 29/08/15
 * Time: 20:252
 */

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Faker\Factory;

class SeriesTest extends ApiTester{

    use DatabaseMigrations;

    /**
     * @group basic
     * @group series-test
     */
    public function test_it_must_be_authenticated(){
        $this->get($this->basic_series_endpoint);
        $this->assertResponseStatus(401);
    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_basic_scoped_tokens_cannot_fetch_admin_scoped_series(){
        $this->seed();

        $this->get($this->admin_series_endpoint, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token]);
        $this->assertResponseStatus(400);//TODO: this should be a 401
    }

    /**
     * @group basic
     * @group series-test
     */
    public function test_it_does_not_accept_put_or_delete_requests_to_the_series_index(){
        $this->seed();

        $this->put($this->basic_series_endpoint)->seeJson();
        $this->assertResponseStatus(405);

        $this->delete($this->basic_series_endpoint)->seeJson();
        $this->assertResponseStatus(405);
    }

    /**
     * @group basic
     * @group series-test
     */
    public function test_a_user_cannot_post_requests_to_a_specific_series(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 1])->id
        ]);

        $this->post($this->basic_series_endpoint.$comic->series->id, [], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(405);
    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_can_create_a_new_series_for_a_comic(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 1])->id
        ]);

        $faker = Factory::create();
        $newSeriesId = $faker->uuid;

        $this->post($this->basic_series_endpoint, [
            'id' => $newSeriesId,
            'comic_id' => $comic->id,
            'series_title' => $faker->sentence(),
            'series_start_year' => $faker->year
        ], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();

        $this->assertResponseStatus(201);

        $this->get($this->basic_series_endpoint.$newSeriesId, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token]);
        $this->assertResponseOk();

        $this->get($this->basic_comic_endpoint.$comic->id, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])
            ->seeJson(['series_id' => $newSeriesId]);
        $this->assertResponseOk();
    }

    /**
     * @group basic
     * @group series-test
     */
    public function test_it_cannot_create_a_series_without_a_valid_parameters(){
        $this->seed();

        $this->post($this->basic_series_endpoint, [], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])
            ->seeJson();

        $this->assertResponseStatus(400);
    }

}
