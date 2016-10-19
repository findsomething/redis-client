<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/10/19
 * Time: 17:28
 */
namespace FSth\Redis\Tests;

use FSth\Redis\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $host = '127.0.0.1';
    private $port = '6379';

    public function setUp()
    {
        $this->client = new Client($this->host, $this->port);
        $this->client->connect();
    }

    public function testSet()
    {
        $this->client->set('hello', 'world');
        $this->assertEquals($this->client->get('hello'), 'world');
        $this->client->del('hello');
        $this->assertEmpty($this->client->get('hello'));

        $error = "";
        $this->client->disconnect();
        try {
            $this->client->set('hello', 'world');
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        $this->assertNotEmpty($error);

        $this->client->reconnect();
        $this->client->set('hello', 'world');
        $this->assertEquals($this->client->get('hello'), 'world');
    }
}