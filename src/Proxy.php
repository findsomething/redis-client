<?php
/**
 * Created by PhpStorm.
 * User: lihan
 * Date: 16/10/19
 * Time: 17:22
 */
namespace FSth\Redis;

class Proxy
{
    protected $maxReconnectTimes = 3;
    protected $storage;
    protected $logger;
    protected $sleep;

    public function __construct(Client $storage, $sleep = true)
    {
        $this->storage = $storage;
        $this->sleep = $sleep;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function __call($method, $args)
    {
        $ok = true;
        $reconnectTimes = 0;

        do {
            if ($ok == false) {
                $reconnectTimes++;
                $this->storage->reconnect();
                $ok = true;
            }

            try {
                return call_user_func_array(array($this->storage, $method), $args);
            } catch (\RedisException $e) {
                $ok = false;
                $this->logger->notice("redis execute error", array(
                    'method' => $method,
                    'args' => $args,
                ));
            }

            if ($reconnectTimes > 1) {
                if ($this->sleep) {
                    sleep(1);
                }
            }

        } while ($ok === false && $reconnectTimes < $this->maxReconnectTimes);

        $this->logger->error("redis reconnect execute error", array(
            'method' => $method,
            'args' => $args,
        ));
    }
}