<?php

namespace Orange\Async\Pool;

use Orange\Config\Config;
use splQueue;

class RedisPool extends Pool
{
    protected $options;

    public function __construct()
    {
        $this->poolQueue = new splQueue();
        $this->taskQueue = new splQueue();

        $config = app('config')->get('database::redis');
        $this->config = $config['default'];
        $this->maxPool = $config['maxPool'];
        $this->timeout = $config['timeout'];

        $this->createResources(true);
    }

    //初始化连接数
    public function createResources($init = false)
    {   
        $ip = $this->config['host'];
        $port = $this->config['port'];
        if (isset($this->config['auth'])) {
            $this->options['password'] = $this->config['auth'];
        }
        $this->options['timeout'] = $this->timeout;

        if ($init) {
            for ($i = $this->ableCount; $i < $this->minPool; $i++) {
                $client = new \swoole_redis($this->options);
                $client->connect($ip, $port, function (\swoole_redis $client, $res) {
                    if ($res === false) {
                        $this->ableCount--;
                        app('syncLog')->error($client->errMsg.':'.$client->errCode);
                        return;
                    }
                    $this->put($client);
                });
                $this->ableCount++;
            }
            return;
        }

        if ($this->ableCount >= $this->maxPool - 1) {
            return;
        }

        $client = new \swoole_redis($this->options);
        $client->connect($ip, $port, function (\swoole_redis $client, $res) {
            if ($res === false) {
                $this->ableCount--;
                app('syncLog')->error($client->errMsg.':'.$client->errCode);
                return;
            }
            $this->put($client);
        });
        $this->ableCount++;
    }

    public function doTask()
    {
        $resource = false;
        while (!$this->poolQueue->isEmpty()) {
            $resource = $this->poolQueue->dequeue();
            if (!isset($this->resources[spl_object_hash($resource)])) {
                $resource = false;
                continue;
            } else {
                break;
            }
        }

        if (!$resource) {
            return;
        }

        $task = $this->taskQueue->dequeue();
        $method = $task['method'];
        $parameters = $task['parameters'];
        $callback = $task['callback'];
        array_push($parameters, function(\swoole_redis $client, $res) use ($callback) {
            if ($res === false) {
                call_user_func_array($callback, array('response' => false, 'error' => $client->errMsg));
            } else {
                call_user_func_array($callback, array('response' => $res, 'error' => null));
            }
            $this->release($client);
        });

        call_user_func_array([$resource, $method], $parameters);
    }

    /**
     * 关闭连接池
     */
    public function close()
    {
        foreach ($this->resources as $conn)
        {
            $conn->close();
        }
    }
}
