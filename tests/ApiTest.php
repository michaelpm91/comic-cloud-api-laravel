<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 20/03/15
 * Time: 01:37
 */
class ApiTest extends TestCase {

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $response = $this->call('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());
    }

}