<?php

namespace Orange\Async\Client;

use Orange\Config\Config;

class Redis implements Base
{
    protected $ip;

    protected $port;

    protected $timeout = 5;

    protected $calltime;

    protected $method;

    protected $parameters;

    protected $options;

    protected $redis;

    protected $connected = false;

    public function __construct()
    {
        $config = app('config')->get('database::redis');
        $this->ip = $config['default']['host'];
        $this->port = $config['default']['port'];
        if (isset($config['default']['auth'])) {
            $this->options['password'] = $config['default']['auth'];
        }
        $this->options['timeout'] = $this->timeout;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        $this->options['timeout'] = $this->timeout;
        $this->redis = new \swoole_redis($this->options);
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function execute(callable $callback, $task)
    {
        if ($this->connected === true) {
            $this->doCallback($callback);
        } else {
            $this->redis->connect($this->ip, $this->port, function (\swoole_redis $client, $res) use ($callback) {
                if ($res === false) {
                    $e = new \Exception($client->errMsg, $client->errCode);
                    call_user_func_array($callback, [false, $e]);
                    return;
                }

                $this->doCallback($callback);
                $this->connected = true;
            });
        }
    }

    public function doCallback($callback)
    {
        $method = $this->method;
        $parameters = $this->parameters;
        array_push($parameters, function(\swoole_redis $client, $res) use ($callback) {
            if ($res === false) {
                $e = new \Exception($client->errMsg, $client->errCode);
                call_user_func_array($callback, [false, $e]);
            } else {
                call_user_func_array($callback, [$res]);
            }
        });

        call_user_func_array([$this->redis, $method], $parameters);
    }

    public function close()
    {
        if ($this->connected === true) {
            $this->redis->close();
        }
    }
}
