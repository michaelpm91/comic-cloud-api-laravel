<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 20/03/15
 * Time: 18:16
 */

use App\Upload;

class UploadTest extends ApiTester {

    /** @test */

    public function it_fetches_uploads(){
        //arrange
        $this->times(5)->makeUpload();
        //Factory::create('App\Upload');

        //act
        $this->getJson('/upload');

        //assert
        $this->assertResponseOk();
    }

    private function makeUpload( $uploadData = []){

        $upload = array_merge([
            'user_id' => 1,
            'file_original_name' => $this->fake->word(3).'.cbz',
            'file_size' => rand(5000, 150000),
            'file_upload_name' => str_random(40).'.cbz'
        ], $uploadData);

        while ($this->times -- ) Upload::create($upload);

    }

}
