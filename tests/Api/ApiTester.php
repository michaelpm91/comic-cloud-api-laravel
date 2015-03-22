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

    protected $fake;

    protected $times = 1;

    function __construct()
    {
        $this->fake = Faker::create();
    }


    protected function times($count)
    {
        $this->times = $count;
        return $this;

    }

    protected function getJson($uri)
    {
        return json_encode($this->call('GET', $uri)->getContent());
    }


}