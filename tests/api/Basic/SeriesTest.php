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
    public function test_it_fetch_all_series(){
        $this->seed();

        $series = factory(App\Models\Series::class, rand(1,10))->create(['user_id' => 1])->each(function($series){
            factory(App\Models\Comic::class, rand(1,10))->create([
                'user_id' => 1,
                'series_id' => $series->id
            ]);
        });

        $this->get($this->basic_series_endpoint, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson([
            'series' => $series->toArray()
        ]);

        $this->assertResponseStatus(200);
    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_fetch_a_series(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 1])->id
        ]);

        $this->get($this->basic_series_endpoint.$comic->series->id, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson([
            'id' => $comic->series->id
        ]);
        $this->assertResponseStatus(200);
    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_cannot_fetch_a_series_that_does_not_exist(){
        $this->seed();

        $this->get($this->basic_series_endpoint."xyz", ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_cannot_fetch_a_series_that_does_belong_to_a_user(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 2,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 2])->id
        ]);

        $this->get($this->basic_series_endpoint.$comic->series->id, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();
        $this->assertResponseStatus(404);
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
            'series_start_year' => $faker->year,
            'comic_vine_series_id' => $faker->randomNumber()
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
    public function test_it_cannot_create_a_new_series_for_a_comic_with_an_invalid_series_id(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 1])->id
        ]);

        $faker = Factory::create();

        $this->post($this->basic_series_endpoint, [
            'id' => 'xyz',
            'comic_id' => $comic->id,
            'series_title' => $faker->sentence(),
            'series_start_year' => $faker->year,
            'comic_vine_series_id' => $faker->randomNumber()
        ], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();

        $this->assertResponseStatus(400);

    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_can_create_a_new_series_for_a_comic_without_a_year(){
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
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_can_update_series_information(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 1])->id
        ]);

        $this->put($this->basic_series_endpoint.$comic->series->id, [
            'series_title' => 'xyz',
            'series_start_year' => date('Y'),
            'series_publisher' => 'xyz',
            'comic_vine_series_id' => rand(1,99999)
        ], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson([

        ]);

        $this->assertResponseStatus(200);
    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_cannot_update_series_information_with_an_empty_body(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 1])->id
        ]);
        $this->put($this->basic_series_endpoint.$comic->series->id, [], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();

        $this->assertResponseStatus(400);
    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_cannot_update_series_information_for_another_user(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 2,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 2])->id
        ]);

        $this->put($this->basic_series_endpoint.$comic->series->id, [
            'series_title' => 'xyz',
            'series_start_year' => date('Y'),
            'series_publisher' => 'xyz',
            'comic_vine_series_id' => rand(1,99999)
        ], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson([

        ]);

        $this->assertResponseStatus(404);
    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_cannot_update_series_information_with_an_invalid_start_year(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 1])->id
        ]);

        $this->put($this->basic_series_endpoint.$comic->series->id, [
            'series_title' => 'xyz',
            'series_start_year' => 'xyz',
            'series_publisher' => 'xyz',
            'comic_vine_series_id' => rand(1,99999)
        ], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson([

        ]);

        $this->assertResponseStatus(400);
    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_cannot_update_series_information_with_an_invalid_comic_vine_id(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 1])->id
        ]);

        $this->put($this->basic_series_endpoint.$comic->series->id, [
            'series_title' => 'xyz',
            'series_start_year' => date('Y'),
            'series_publisher' => 'xyz',
            'comic_vine_series_id' => 'xyz'
        ], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson([

        ]);

        $this->assertResponseStatus(400);
    }
    /**
     * @group lolz
     * @group basic
     * @group series-test
     */
    public function test_a_user_can_delete_a_series_and_associated_comics(){
        $this->seed();

        $comic = factory(App\Models\Comic::class)->create([
            'user_id' => 1,
            'series_id' => factory(App\Models\Series::class)->create(['user_id' => 1])->id
        ]);

        $this->delete($this->basic_series_endpoint.$comic->series_id, [], ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();

        $this->get($this->basic_comic_endpoint.$comic->id, ['HTTP_Authorization' => 'Bearer '. $this->test_basic_access_token])->seeJson();

        $this->assertResponseStatus(404);

    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_a_user_cannot_delete_a_series_that_belongs_to_them(){

    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_a_user_cannot_delete_a_series_that_does_not_exist(){

    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_can_fetch_meta_data_for_a_series_that_exists(){

    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_cannot_fetch_meta_data_for_a_series_that_does_not_exist(){

    }
    /**
     * @group basic
     * @group series-test
     */
    public function test_it_cannot_fetch_meta_data_for_series_that_does_not_belong_to_the_user(){

    }
}
