<?php

namespace Orange\Async\Client;

use Orange\Async\Pool\Result;
use Orange\Promise\Promise;

class MysqlPromise
{
    protected $timeout;

    protected $config;

    protected $mysql;

    protected $connected = false;

    public function __construct($options, $timeout = 2000)
    {
        $this->timeout = $timeout;
        $this->config = [
            'host' => $options['host'],
            'port' => $options['port'],
            'user' => $options['user'],
            'password' => $options['password'],
            'database' => $options['dbname'],
            'charset' => $options['charset'],
            'timeout' => $this->timeout,
        ];
        $this->mysql = new \swoole_mysql;
        $this->mysql->on('close', [$this, 'onClose']);
    }

    public function connect()
    {
        $promise = Promise::deferred();
        $this->mysql->connect($this->config, function(\swoole_mysql $db, $result) use ($promise) {
            if ($db->errno === 0) {
                $this->connected = true;
                $promise->resolve(true);
            } else {
                $e = new \Exception($db->connect_error, $db->connect_errno);
                $promise->reject($e);
            }
        });

        return Promise::race([$promise, timeout($this->timeout)]);
    }

    protected function execute($sql)
    {
        $promise = Promise::deferred();
        $this->mysql->query($sql, function(\swoole_mysql $db, $result) use ($promise) {
            if ($db->errno === 0) {
                $result = new Result($result, $db->affected_rows, $db->insert_id);
                $promise->resolve($result);
            } else {
                if ($db->errno == 2006 || $db->errno == 2013) {

                }
                $e = new \Exception($db->error, $db->errno);
                $promise->reject($e);
            }
        });
        return Promise::race([$promise, timeout($this->timeout)]);
    }

    public function begin()
    {
        $promise = Promise::deferred();
        $this->mysql->begin(function (\swoole_mysql $db, $result) use ($promise) {
            if ($db->errno === 0) {
                $promise->resolve(true);
            } else {
                $e = new \Exception($db->error, $db->errno);
                $promise->reject($e);
            }
        });

        return Promise::race([$promise, timeout($this->timeout)]);
    }

    public function rollback()
    {
        $promise = Promise::deferred();
        $this->mysql->rollback(function (\swoole_mysql $db, $result) use ($promise) {
            if ($db->errno === 0) {
                $promise->resolve(true);
            } else {
                $e = new \Exception($db->error, $db->errno);
                $promise->reject($e);
            }
        });

        return Promise::race([$promise, timeout($this->timeout)]);
    }

    public function commit()
    {
        $promise = Promise::deferred();
        $this->mysql->commit(function (\swoole_mysql $db, $result) use ($promise) {
            if ($db->errno === 0) {
                $promise->resolve(true);
            } else {
                $e = new \Exception($db->error, $db->errno);
                $promise->reject($e);
            }
        });

        return Promise::race([$promise, timeout($this->timeout)]);
    }

    protected function onClose(\swoole_mysql $mysql)
    {
        $this->connected = false;
    }

    public function isConnected()
    {
        return $this->connected;
    }

    public function close()
    {
        if ($this->connected === true) {
            $this->mysql->close();
            $this->connected = false;
        }
    }

}
