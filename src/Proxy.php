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
    const MIN_MICROSECOND = 10;

    protected $maxReconnectTimes = 3;
    protected $storage;
    protected $logger;
    protected $sleep;
    protected $sleepTime;
    protected $free;

    /**
     * Proxy constructor.
     * @param Client $storage
     * @param bool $free
     *  true/false whether free connection each time
     */
    public function __construct(Client $storage, $free = false)
    {
        $this->storage = $storage;
        $this->free = $free;
        $this->sleep = true;
        $this->sleepTime = self::MIN_MICROSECOND;
    }

    public function setSleep($sleep)
    {
        $this->sleep = $sleep;
        return $this;
    }

    public function setSleepTime($sleepTime)
    {
        $this->sleepTime = max($sleepTime, self::MIN_MICROSECOND);
        return $this;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function setFreeNow($free)
    {
        $this->free = $free;
    }

    public function __call($method, $args)
    {
        $ok = true;
        $reconnectTimes = 0;
        $exception = null;

        do {
            try {
                if ($this->free) {
                    $this->storage->connect();
                }

                if ($ok == false) {
                    $reconnectTimes++;
                    $this->storage->reconnect();
                    $ok = true;
                }

                $result = call_user_func_array(array($this->storage, $method), $args);

                if ($this->free) {
                    $this->storage->disconnect();
                }

                return $result;
            } catch (\RedisException $e) {
                $exception = $e;
                $ok = false;
            }

            if ($reconnectTimes > 1) {
                if ($this->sleep) {
                    usleep($this->getRandMicroSecond());
                }
            }

        } while ($ok === false && $reconnectTimes < $this->maxReconnectTimes);

        $this->logger->error("redis reconnect execute error", array(
            'method' => $method,
            'args' => $args,
        ));
        throw $exception;
    }

    private function getRandMicroSecond()
    {
        return rand(self::MIN_MICROSECOND, $this->sleepTime) * 1000;
    }
}