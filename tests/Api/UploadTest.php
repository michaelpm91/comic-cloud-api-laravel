<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 20/03/15
 * Time: 18:16
 */

use App\Upload;
use Laracasts\TestDummy\Factory;

class UploadTest extends ApiTester {

    protected $user;

    /*public function setUp(){//runs per test :(
        parent::setUp();
        $this->user = Factory::create('App\User');

    }*/
    public function test_must_be_authenticated(){

    }

    public function test_it_creates_upload(){

    }
    public function test_it_creates_uploads(){

    }
    public function test_it_fetches_uploads(){//Retrieve all user uploads
        //arrange
        $upload = Factory::times(10)->create('App\Upload');

        //act
        $response = $this->getJson('/upload');
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
