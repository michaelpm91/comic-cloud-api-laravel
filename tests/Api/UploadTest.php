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

    }
    public function test_it_creates_upload(){
        //arrange

        //act
        //$uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile('/path/to/file', 'original-file-name.ext');
        //$response = $this->postRequest('/upload', ['file' => 'comic'], ['file' => $uploadedFile]);

        //dd($response);

        //assert
        //$this->assertResponseOk();


    }
    public function test_it_creates_uploads(){

    }
    public function test_it_fetches_uploads(){//Retrieve all user uploads
        //arrange
        $upload = Factory::times(10)->create('App\Upload', ['user_id' => $this->user->id]);

        //act
        $response = $this->getRequest('/upload');

        //dd($response);

        //assert
        $this->assertResponseOk();
    }
    public function test_it_fetches_upload(){//Retrieve single upload
        //arrange
        //$upload = Factory::create('App\Upload');

    }
    public function test_it_updates_upload(){

    }
    public function test_it_updates_uploads(){

    }
    public function test_it_deletes_upload(){

    }
    public function test_it_deletes_uploads(){

    }

}
