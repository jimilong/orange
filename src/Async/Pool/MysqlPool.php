<?php

namespace Orange\Async\Pool;

class MysqlPool extends Pool
{
    //MYSQL事务
    protected $transaction = [];

    public function __construct()
    {
        $this->poolQueue = new \splQueue();
        $this->taskQueue = new \splQueue();
        $config = app('config')->get('database::pdo');
        $this->minPool = $config['minPool'];
        $this->maxPool = $config['maxPool'];
        $this->timeout = $config['timeout'];
        $this->config = [
            'host' => $config['default']['host'],
            'port' => $config['default']['port'],
            'user' => $config['default']['user'],
            'password' => $config['default']['password'],
            'database' => $config['default']['dbname'],
            'charset' => $config['default']['charset'],
            'timeout' => $this->timeout,
        ];

        //初始化连接数
        $this->createResources(true);
    }

    public function createResources($init = false)
    {
        if ($init) {
            for ($i = $this->ableCount; $i < $this->minPool; $i++) {
                $mysql = new \swoole_mysql;
                $mysql->connect($this->config, function(\swoole_mysql $mysql, $res) {
                    if ($res === false) {
                        $this->ableCount--;
                        app('syncLog')->error($mysql->connect_error.':'.$mysql->connect_errno);
                        return;
                    }
                    $this->put($mysql);
                });
                $this->ableCount++;
            }

            return;
        }

        if ($this->ableCount >= $this->maxPool - 1) {
            return;
        }

        $mysql = new \swoole_mysql;
        $mysql->connect($this->config, function(\swoole_mysql $mysql, $res) {
            if ($res === false) {
                $this->ableCount--;
                app('syncLog')->error($mysql->connect_error.':'.$mysql->connect_errno);
                return;
            }
            $this->put($mysql);
        });
        $this->ableCount++;
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
                    call_user_func_array($callback, array('response' => false, 'error' => $mysql->error));
                    if (! isset($this->transaction[$taskId])) {
                        $this->release($mysql);
                    }
                    return;
                }
                $result = new Result($res, $mysql->affected_rows, $mysql->insert_id);
                call_user_func_array($callback, array('response' => $result, 'error' => null));

                //若不存在事务则释放资源
                if (! isset($this->transaction[$taskId])) {
                    $this->release($mysql);
                }
            });
        } else {
            $resource->$methd(function(\swoole_mysql $mysql, $res) use ($callback, $methd, $taskId) {
                if ($res === false) {
                    //TODO begin rollback commit 会失败吗
                    call_user_func_array($callback, array('response' => false, 'error' => $mysql->error));
                    if ($methd != 'begin') {
                        $this->release($mysql);
                        unset($this->transaction[$taskId]);
                    }
                    return;
                }
                $result = new Result($res, $mysql->affected_rows, $mysql->insert_id);
                call_user_func_array($callback, array('response' => $result, 'error' => null));
                //存在事务
                if ($methd != 'begin') {
                    //释放资源
                    $this->release($mysql);
                    unset($this->transaction[$taskId]);
                }
            });
        }
    }

    /**
     * 关闭连接池
     */
    public function close()
    {
        foreach ($this->resources as $conn)
        {
            if ($conn->connected) {
                $conn->close();
            }
        }
    }
}
