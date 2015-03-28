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
        //$uploadedFile = new Symfony\Component\HttpFoundation\File\UploadedFile('/path/to/file', 'original-file-name.ext');
        //$response = $this->postRequest('/upload', ['file' => 'comic'], ['file' => $uploadedFile]);

        //dd($response);

        //assert
        //$this->assertResponseOk();


    }
    public function test_it_creates_uploads(){//multiple uplaods

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
        //$this->assertArrayHasKey('foo', array('bar' => 'baz'));
        //$this->assertEquals(true, $result);




    }
    public function test_it_fetches_user_upload_only(){//
        //arrange
        $upload = Factory::create('App\Upload', ['user_id' => 2]);

        //act
        $response = $this->getRequest('/upload/'.$upload->id);

        //assert
        $this->assertResponseStatus(404);//TODO: This will need to be updated when API returns are madem ore consistent

    }

}
