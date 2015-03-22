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


    protected function getRequest($uri, $auth_header = '')
    {
        return json_encode($this->call('GET', $uri)->getContent(), [], ['Authorization' => $auth_header]);
    }

    protected function postRequest($uri, $data = [], $auth_header = '')
    {
        return json_encode($this->call('POST', $uri, $data)->getContent(), [], ['Authorization' => $auth_header]);
    }


}