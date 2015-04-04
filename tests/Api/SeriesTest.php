<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 04/04/15
 * Time: 21:31
 */

use Laracasts\TestDummy\Factory;

use App\User;
use App\Series;

class SeriesTest extends ApiTester {


    protected $auth_header;
    protected $user;

    public function setUp(){//runs per test :(
        parent::setUp();
        Artisan::call('db:seed');//TODO: Would be nice to move this...
        $this->user = User::find(1);
    }
    public function test_it_must_be_authenticated(){
        //arrange
        $this->test_access_token = "";

        //act
        $response = $this->getRequest('/series');

        //assert
        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are made more consistent

    }

    //public function test_ (){ }
    public function test_it_can_create_a_new_series_for_a_comic (){

    }
    public function test_it_cannot_create_an_orphan_series (){

    }
    public function test_it_cannot_create_a_series_without_an_id (){

    }
    public function test_it_cannot_create_a_series_without_a_comic_id (){

    }
    public function test_it_cannot_create_a_series_without_a_series_title (){

    }
    public function test_it_can_create_a_series_with_a_start_year (){

    }
    public function test_it_can_create_a_series_without_a_start_year_field (){

    }
    public function test_it_will_generate_a_new_series_id_when_a_duplicate_is_passed_when_creating_a_new_series (){

    }
    public function test_it_can_fetch_all_series (){

    }
    public function test_it_can_fetch_a_specific_series (){

    }
    public function test_it_cannot_fetch_a_series_that_does_not_exist (){

    }
    public function test_it_cannot_fetch_a_series_that_does_not_belong_to_the_user (){

    }

    public function test_it_can_update_a_series_title (){

    }
    public function test_it_can_update_a_series_start_year (){

    }
    public function test_it_can_update_a_series_publisher (){

    }
    public function test_it_cannot_update_a_series_title_that_does_not_belong_to_the_user (){

    }
    public function test_it_cannot_update_a_series_start_year_that_does_not_belong_to_the_user (){

    }
    public function test_it_cannot_update_a_series_publisher_that_does_not_belong_to_the_user (){

    }
    public function test_it_can_delete_a_series_and_associated_comics (){

    }
    public function test_it_cannot_delete_a_series_that_does_not_belong_to_the_user (){

    }
}
 