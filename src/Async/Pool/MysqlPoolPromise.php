<?php

namespace Orange\Async\Pool;

use Orange\Async\Client\MysqlPromise;

class MysqlPoolPromise
{
    //splQueue
    protected $poolQueue;

    //splQueue
    protected $taskQueue;

    //最大连接数
    protected $maxPool = 5;

    protected $minPool = 2;

    //配置
    protected $config;

    //连接池资源
    protected $resources = [];

    protected $idleCount = 0;

    protected $timeout = 2000;
    //MYSQL事务
    protected $transaction = [];

    public function __construct($config)
    {
        $this->poolQueue = new \splQueue();
        $this->taskQueue = new \splQueue();
        $this->config = [
            'host' => $config['default']['host'],
            'port' => $config['default']['port'],
            'user' => $config['default']['user'],
            'password' => $config['default']['password'],
            'database' => $config['default']['dbname'],
            'charset' => $config['default']['charset'],
        ];

        //初始化连接数
        $this->init();
    }

    protected function init()
    {
        for ($i = $this->idleCount; $i < $this->minPool; $i++) {
            $mysql = new MysqlPromise($this->config, $this->timeout);
            $promise = $mysql->connect();
            $promise->then(function ($value) use ($mysql) {
                $this->put($mysql);
                $this->idleCount++;
            })->eCatch(function ($e) {
                //todo log error
            });
        }
    }

    public function create()
    {
        if (count($this->resources) >= $this->maxPool) {
            return;
        }

        $mysql = new MysqlPromise($this->config, $this->timeout);
        $promise = $mysql->connect();
        $promise->then(function ($value) use ($mysql) {
            $this->put($mysql);
            $this->idleCount++;
        })->eCatch(function ($e) {
            //todo log error
        });
    }

    public function put($resource)
    {
        $this->resources[spl_object_hash($resource)] = $resource;
        $this->poolQueue->enqueue($resource);

        if (!$this->taskQueue->isEmpty()) {
            $this->doTask();
        }
    }

    public function release($resource)
    {
        $this->poolQueue->enqueue($resource);

        if (!$this->taskQueue->isEmpty()) {
            $this->doTask();
        }
    }

    public function remove($resource)
    {
        unset($this->resources[spl_object_hash($resource)]);
        $this->idleCount--;
        $resource->close();
    }

    public function close()
    {
        foreach ($this->resources as $conn)
        {
            $conn->close();
        }
    }



    public function doTask()
    {
        $resource = false;
        $task = $this->taskQueue->dequeue();
        $taskId = $task['taskId'];
        $methd = $task['method'];
        //存在事务
        if (isset($this->transaction[$taskId])) {
            $resource = $this->transaction[$taskId];
        } else {
            while (!$this->poolQueue->isEmpty()) {
                $resource = $this->poolQueue->dequeue();
                if (!isset($this->resources[spl_object_hash($resource)])) {
                    $resource = false;
                    continue;
                }
                if ($resource->connected === false) {
                    $this->remove($resource);
                    $resource = false;
                    continue;
                } else {
                    break;
                }
            }

            if (!$resource) {
                $this->taskQueue->enqueue($task);
                return;
            }
        }

        //开始事务锁定一条MYSQL链接
        if ($methd == 'begin') {
            $this->transaction[$taskId] = $resource;
        }
        $callback = $task['callback'];
        if ($methd == 'query') {
            $resource->$methd($task['parameters'], function(\swoole_mysql $mysql, $res) use ($callback, $methd, $taskId) {
                if ($res === false) {
                    //TODO begin rollback commit 会失败吗
                    $e = new \Exception($mysql->error, $mysql->errno);
                    call_user_func_array($callback, [false, $e]);
                    if (! isset($this->transaction[$taskId])) {
                        $this->release($mysql);
                    }
                    return;
                }
                $result = new Result($res, $mysql->affected_rows, $mysql->insert_id);
                call_user_func_array($callback, [$result]);

                //若不存在事务则释放资源
                if (! isset($this->transaction[$taskId])) {
                    $this->release($mysql);
                }
            });
        } else {
            $resource->$methd(function(\swoole_mysql $mysql, $res) use ($callback, $methd, $taskId) {
                if ($res === false) {
                    $e = new \Exception('mysql '.$methd.' error' ,22);
                    call_user_func_array($callback, [false, $e]);
                    if ($methd != 'begin') {
                        $this->release($mysql);
                        unset($this->transaction[$taskId]);
                    }
                    return;
                }
                $result = new Result($res, $mysql->affected_rows, $mysql->insert_id);
                call_user_func_array($callback, [$result]);
                //存在事务
                if ($methd != 'begin') {
                    //释放资源
                    $this->release($mysql);
                    unset($this->transaction[$taskId]);
                }
            });
        }
    }
}
