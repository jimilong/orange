<?php

namespace Orange\Async\Client;

use Orange\Async\Pool\Result;
use Orange\Config\Config;

class Mysql implements Base
{
    protected $timeout = 5;

    protected $calltime;

    protected $config;

    protected $sql;

    protected $mysql;

    public function __construct()
    {
        $config = app('config')->get('database::pdo');
        $this->config = [
            'host' => $config['default']['host'],
            'port' => $config['default']['port'],
            'user' => $config['default']['user'],
            'password' => $config['default']['password'],
            'database' => $config['default']['dbname'],
            'charset' => $config['default']['charset'],
            'timeout' => $this->timeout,
        ];

        $this->mysql = new \swoole_mysql;
        $this->mysql->connected = false;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        $this->config['timeout'] = $timeout;
    }

    public function query($sql)
    {
        $this->sql = $sql;
    }

    public function execute(callable $callback, $task)
    {
        $this->calltime = microtime(true);

        if ($this->mysql->connected === true) {
            $this->doCallback($callback);
        } else {
            $this->mysql->connect($this->config, function(\swoole_mysql $mysql, $res) use ($callback) {
                if ($res === false) {
                    call_user_func_array($callback, array('response' => false, 'error' => "connect to mysql server failed", 'calltime' => 0));
                    return;
                }

                $this->doCallback($callback);
            });
        }
    }

    public function doCallback($callback)
    {
        if ($this->sql == "begin") {
            $this->mysql->begin(function(\swoole_mysql $mysql, $res) use ($callback) {
                if ($res === false) {
                    call_user_func_array($callback, array('response' => false, 'error' => $mysql->error));
                    return;
                }
                call_user_func_array($callback, array('response' => true, 'error' => null));
            });
            return;
        }

        if ($this->sql == "commit") {
            $this->mysql->commit(function(\swoole_mysql $mysql, $res) use ($callback) {
                if ($res === false) {
                    call_user_func_array($callback, array('response' => false, 'error' => $mysql->error));
                    return;
                }
                call_user_func_array($callback, array('response' => true, 'error' => null));
            });
            return;
        }

        if ($this->sql == "rollback") {
            $this->mysql->rollback(function(\swoole_mysql $mysql, $res) use ($callback) {
                if ($res === false) {
                    call_user_func_array($callback, array('response' => false, 'error' => $mysql->error));
                    return;
                }
                call_user_func_array($callback, array('response' => true, 'error' => null));
            });
            return;
        }

        $this->mysql->query($this->sql, function(\swoole_mysql $mysql, $res) use ($callback) {
            $this->calltime = microtime(true) - $this->calltime;
            if ($res === false) {
                call_user_func_array($callback, array('response' => false, 'error' => $mysql->error, 'calltime' => $this->calltime));
                return;
            }
            $result = new Result($res, $mysql->affected_rows, $mysql->insert_id);
            call_user_func_array($callback, array('response' => $result, 'error' => null, 'calltime' => $this->calltime));
        });
    }

    public function close()
    {
        if ($this->mysql->connected === true) {
            $this->mysql->close();
        }
    }
}
