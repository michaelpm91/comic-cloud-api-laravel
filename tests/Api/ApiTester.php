<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 21/03/15
 * Time: 14:22
 */

use Faker\Factory as Faker;

use Laracasts\TestDummy\DbTestCase;

class ApiTester extends DBTestCase {

    protected $test_access_token = 'test_access_token';


    protected function getRequest($uri)
    {
        return json_encode($this->call('GET', $uri, [], [], [],['HTTP_Authorization' => 'Bearer '.$this->test_access_token])->getContent());
    }

    protected function postRequest($uri, $data = [], $files =[])
    {
        return json_encode($this->call('POST', $uri, $data, [], $files, ['HTTP_Authorization' => 'Bearer '.$this->test_access_token])->getContent());
    }


}