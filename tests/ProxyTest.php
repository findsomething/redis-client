<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/10/19
 * Time: 17:44
 */
namespace FSth\Redis\Tests;

use FSth\Redis\Client;
use FSth\Redis\Proxy;

class ProxyTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $proxy;

    private $host = '127.0.0.1';
    private $port = '6379';

    public function setUp()
    {
        $this->client = new Client($this->host, $this->port);
        $this->proxy = new Proxy($this->client);
        $this->proxy->setLogger(new FakeLogger());
    }

    public function testSet()
    {
        $this->proxy->set('hello', 'world');
        $this->assertEquals($this->proxy->get('hello'), 'world');
        $this->proxy->del('hello');
        $this->assertEmpty($this->proxy->get('hello'));

        $error = "";
        $this->proxy->disconnect();
        try {
            $this->proxy->set('hello', 'world');
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        $this->assertEmpty($error);

        $this->client->set('hello', 'world');
        $this->assertEquals($this->proxy->get('hello'), 'world');
    }
}