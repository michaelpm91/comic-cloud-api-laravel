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

    public function setUp(){//runs per test :(
        parent::setUp();
        Artisan::call('db:seed');//TODO: Would be nice to move this...
        $this->user = User::find(1);
        //User::create(['username'=>'user', 'email' => 'email@email.co', 'password' => '1234']);
        //dd(User::find(1));
        Auth::loginUsingId($this->user->id);

    }
    public function test_must_be_authenticated(){
        //arrange
        Auth::logout();
        $this->test_access_token = "";

        //act
        $response = $this->getRequest('/upload');

        //assert
        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are madem ore consistent


    }
    public function test_it_creates_upload(){
        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest('/upload', [
            "exists" => false,
            "series_id" => "12345",
            "comic_id" => "1",
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

        $response = $this->postRequest('/upload', [
            "exists" => "",
            "series_id" => "12345",
            "comic_id" => "1",
            "series_title" => "test",
            "series_start_year" => "2015",
            "comic_issue" => 1
        ], ['file' => $uploadedFile]);


        //assert

        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are madem ore consistent

    }

    public function test_uploads_must_have_match_data_series_id(){

        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest('/upload', [
            "exists" => false,
            "series_id" => "",
            "comic_id" => "1",
            "series_title" => "test",
            "series_start_year" => "2015",
            "comic_issue" => 1
        ], ['file' => $uploadedFile]);


        //assert

        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are madem ore consistent

    }
    public function test_uploads_must_have_match_data_comic_id(){

        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest('/upload', [
            "exists" => false,
            "series_id" => "12345",
            "comic_id" => "",
            "series_title" => "test",
            "series_start_year" => "2015",
            "comic_issue" => 1
        ], ['file' => $uploadedFile]);


        //assert

        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are madem ore consistent

    }
    public function test_uploads_must_have_match_data_series_title(){

        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest('/upload', [
            "exists" => false,
            "series_id" => "12345",
            "comic_id" => "1",
            "series_title" => "",
            "series_start_year" => "2015",
            "comic_issue" => 1
        ], ['file' => $uploadedFile]);


        //assert

        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are madem ore consistent

    }
    public function test_uploads_must_have_match_data_series_start_year(){

        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest('/upload', [
            "exists" => false,
            "series_id" => "12345",
            "comic_id" => "1",
            "series_title" => "test",
            "series_start_year" => "",
            "comic_issue" => 1
        ], ['file' => $uploadedFile]);


        //assert

        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are madem ore consistent

    }
    public function test_uploads_must_have_match_data_comic_issue(){

        //arrange

        //act
        $uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile(storage_path()."/test files/test-comic-6-pages.cbz", 'test-comic-6-pages.cbz');

        $response = $this->postRequest('/upload', [
            "exists" => false,
            "series_id" => "12345",
            "comic_id" => "1",
            "series_title" => "test",
            "series_start_year" => "2015",
            "comic_issue" => ""
        ], ['file' => $uploadedFile]);


        //assert

        $this->assertResponseStatus(400);//TODO: This will need to be updated when API returns are madem ore consistent

    }

    public function test_it_fetches_uploads(){//Retrieve all user uploads
        //arrange
        $upload = Factory::times(10)->create('App\Upload', ['user_id' => $this->user->id]);

        //act
        $response = $this->getRequest('/upload');

        //assert
        $this->assertResponseOk();
    }
    public function test_it_fetches_upload(){//Retrieve single upload
        //arrange
        $upload = Factory::create('App\Upload', ['user_id' => $this->user->id]);

        //act
        $response = $this->getRequest('/upload/'.$upload->id);

        //assert
        $this->assertResponseOk();

    }
    public function test_it_fetches_user_uploads_only(){//
        //arrange
        $user_uploads = Factory::times(5)->create('App\Upload', ['user_id' => $this->user->id]);
        $other_user_upload = Factory::create('App\Upload', ['user_id' => 2]);

        //act
        $response = $this->getRequest('/upload');

        //assert
        $result = false;
        foreach(json_decode($response, true)['Uploads'] as $upload){
            if($upload['id'] == $other_user_upload->id) $result = true;
        }
        $this->assertEquals(false, $result);



    }
    public function test_it_fetches_user_upload_only(){//
        //arrange
        $upload = Factory::create('App\Upload', ['user_id' => 2]);

        //act
        $response = $this->getRequest('/upload/'.$upload->id);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are made more consistent

    }

}
