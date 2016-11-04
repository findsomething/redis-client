<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/10/19
 * Time: 17:19
 */
namespace FSth\Redis;

class Client
{
    private $host;
    private $port;
    private $timeout;
    private $redis;

    public function __construct($host, $port, $timeout = 10)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->connect();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function connect()
    {
        $this->redis = new \Redis();
        $result = $this->redis->connect($this->host, $this->port, $this->timeout);
        if (!$result) {
            throw new \RedisException("connect redis failed");
        }
        $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
    }

    public function reconnect()
    {
        $this->disconnect();
        $this->connect();
    }

    public function disconnect()
    {
        try {
            if ($this->checkValid()) {
                $this->redis->close();
            }
        } catch (\Exception $e) {

        } finally {
            $this->redis = null;
        }
    }

    public function __call($method, $args)
    {
        if ($this->checkValid()) {
            return call_user_func_array(array($this->redis, $method), $args);
        }
        throw new \RedisException("execute {$method} failed");
    }

    private function checkValid()
    {
        return (!empty($this->redis) && ($this->redis instanceof \Redis)) ? true : false;
    }
}