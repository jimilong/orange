<?php

namespace Orange\Async\Client;

use Orange\Promise\Promise;

class RedisPromise
{
    protected $ip;

    protected $port;

    protected $timeout;

    protected $redis;

    protected $auth;

    protected $connected = false;

    public function __construct($ip, $port, $auth = '', $timeout = 2000)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->auth = $auth;
        $this->timeout = $timeout;
    }

    public function __call($name, $params)
    {
        $promise = Promise::deferred();
        if ($this->connected === true) {
            $this->handle($name, $params, $promise);
        } else {
            $this->redis = new \swoole_redis(['password' => $this->auth, 'timeout' => $this->timeout]);
            $this->redis->on('close', [$this, 'onClose']);
            $this->redis->connect($this->ip, $this->port, function (\swoole_redis $client, $res) use ($name, $params, $promise) {
                if ($res === false) {
                    $e = new \Exception($client->errMsg, $client->errCode);
                    $promise->reject($e);
                    return;
                }

                $this->handle($name, $params, $promise);
                $this->connected = true;
            });
        }

        return Promise::race([$promise, timeout($this->timeout)]);
    }

    protected function handle($name, $params, $promise)
    {
        array_push($params, function(\swoole_redis $client, $res) use ($promise) {
            if ($res === false) {
                $e = new \Exception($client->errMsg, $client->errCode);
                $promise->reject($e);
            } else {
                $promise->resolve($res);
            }
        });

        call_user_func_array([$this->redis, $name], $params);
    }

    protected function onClose(\swoole_redis $redis)
    {
        $this->connected = false;
    }

    public function close()
    {
        if ($this->connected === true) {
            $this->redis->close();
        }
    }
}
