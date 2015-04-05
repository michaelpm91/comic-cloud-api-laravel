<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 20/03/15
 * Time: 18:16
 */

use App\Upload;
use App\User;
use Laracasts\TestDummy\Factory;


class UploadTest extends ApiTester {

    protected $user;
    protected $auth_header;
    protected $upload_endpoint = "/upload/";

    public function setUp(){//runs per test :(
        parent::setUp();
        Artisan::call('db:seed');//TODO: Would be nice to move this...
        $this->user = User::find(1);
    }
    public function test_it_must_be_authenticated(){
        //arrange
        $this->test_access_token = "";

        //act
        $response = $this->getRequest($this->upload_endpoint);

        //assert
        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are made more consistent


    }
    public function test_it_creates_upload(){
        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest($this->upload_endpoint, [
            "exists" => false,
            "series_id" => "0000000000000000000000000000000000000000",
            "comic_id" => "1111111111111111111111111111111111111111",
            "series_title" => "test",
            "series_start_year" => "2015",
            "comic_issue" => 1
          ], ['file' => $uploadedFile]);

        //assert
        $this->assertResponseStatus(201);


    }
    public function test_uploads_must_have_match_data_exists(){

        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest($this->upload_endpoint, [
            "exists" => "",
            "series_id" => "12345",
            "comic_id" => "1",
            "series_title" => "test",
            "series_start_year" => "2015",
            "comic_issue" => 1
        ], ['file' => $uploadedFile]);


        //assert

        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_uploads_must_have_match_data_series_id(){

        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest($this->upload_endpoint, [
            "exists" => false,
            "series_id" => "",
            "comic_id" => "1",
            "series_title" => "test",
            "series_start_year" => "2015",
            "comic_issue" => 1
        ], ['file' => $uploadedFile]);


        //assert

        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_uploads_must_have_match_data_comic_id(){

        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest($this->upload_endpoint, [
            "exists" => false,
            "series_id" => "12345",
            "comic_id" => "",
            "series_title" => "test",
            "series_start_year" => "2015",
            "comic_issue" => 1
        ], ['file' => $uploadedFile]);


        //assert

        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_uploads_must_have_match_data_series_title(){

        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest($this->upload_endpoint, [
            "exists" => false,
            "series_id" => "12345",
            "comic_id" => "1",
            "series_title" => "",
            "series_start_year" => "2015",
            "comic_issue" => 1
        ], ['file' => $uploadedFile]);


        //assert

        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_uploads_must_have_match_data_series_start_year(){

        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest($this->upload_endpoint, [
            "exists" => false,
            "series_id" => "12345",
            "comic_id" => "1",
            "series_title" => "test",
            "series_start_year" => "",
            "comic_issue" => 1
        ], ['file' => $uploadedFile]);


        //assert

        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_uploads_must_have_match_data_comic_issue(){

        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest($this->upload_endpoint, [
            "exists" => false,
            "series_id" => "12345",
            "comic_id" => "1",
            "series_title" => "test",
            "series_start_year" => "2015",
            "comic_issue" => ""
        ], ['file' => $uploadedFile]);


        //assert

        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_fetches_all_uploads(){//Retrieve all user uploads
        //arrange
        $mocked_uploads = Factory::times(10)->create('App\Upload', ['user_id' => $this->user->id]);

        //act
        $response = $this->getRequest($this->upload_endpoint);

        //assert
        $result = true;
        foreach($mocked_uploads as $mocked_upload ){
            if (!in_array($mocked_upload->id, json_decode($response, true)['uploads'])) {
                $result = false;
                break;
            }
        }
        $this->assertResponseOk();
        $this->assertEquals(false, $result);

    }
    public function test_it_fetches_upload(){//Retrieve single upload
        //arrange
        $upload = Factory::create('App\Upload', ['user_id' => $this->user->id]);

        //act
        $response = $this->getRequest($this->upload_endpoint.$upload->id);

        //assert
        $this->assertResponseOk();
        $this->assertEquals($upload->id, json_decode($response, true)['upload']['id']);
        $this->assertEquals($upload->file_original_name, json_decode($response, true)['upload']['file_original_name']);

    }
    public function test_it_cannot_fetch_an_upload_that_does_not_exist(){

        //arrange

        //act
        $response = $this->getRequest($this->upload_endpoint.str_random(40));

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }
    public function test_it_fetches_user_uploads_only(){//
        //arrange
        $user_uploads = Factory::times(5)->create('App\Upload', ['user_id' => $this->user->id]);
        $other_user_upload = Factory::create('App\Upload', ['user_id' => 2]);

        //act
        $response = $this->getRequest($this->upload_endpoint);

        //assert
        $result = false;
        foreach(json_decode($response, true)['uploads'] as $upload){
            if($upload['id'] == $other_user_upload->id) $result = true;
        }
        $this->assertEquals(false, $result);

    }
    public function test_it_fetches_user_upload_only(){//
        //arrange
        $upload = Factory::create('App\Upload', ['user_id' => 2]);

        //act
        $response = $this->getRequest($this->upload_endpoint.$upload->id);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }

}
