<?php

namespace Orange\Async\Client;

use Orange\Config\Config;

class Tcp implements Base
{
    protected $ip;

    protected $port;

    protected $data;

    protected $timeout = 5;

    protected $calltime;

    protected $client;

    protected $isFinish = false;

    protected $timeId;

    protected $count = 1;

    protected $return;

    public function __construct()
    {
        $rpc = app('config')->get('app::rpc');
        $this->ip = $rpc['ip'];
        $this->port = $rpc['port'];

        $this->client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->client->set([
            'open_length_check'     => true,
            'package_length_type'   => 'n',
            'package_length_offset' => 0,       //第N个字节是包长度的值
            'package_body_offset'   => 2,       //第几个字节开始计算长度
            'package_max_length'    => 1024 * 8  //协议最大长度 8K
        ]);
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function parse($data)
    {
        return $data;
    }

    public function execute(callable $callback, $task)
    {
        $this->client->on("connect", function ($cli) use ($callback) {
            $this->calltime = microtime(true);
            $cli->send($this->data);

            $this->timeId = swoole_timer_after(floatval($this->timeout) * 1000, function () use ($callback) {
                if (!$this->isFinish) {
                    $this->client->close();
                    $this->isFinish = true;
                    call_user_func_array($callback, array('response' => false, 'error' => 'timeout', 'calltime' => $this->calltime));
                }
            });
        });

        $this->client->on('close', function ($cli) {
        });

        $this->client->on('error', function ($cli) use ($callback) {
            $this->calltime = microtime(true) - $this->calltime;
            call_user_func_array($callback, array('response' => false, 'error' => socket_strerror($cli->errCode), 'calltime' => $this->calltime));
        });

        $this->client->on("receive", function ($cli, $data) use ($callback) {
            if (!$this->isFinish) {

                $data = $this->parse($data);//todo
                $this->return[] = $data;

                $this->count--;
                if ($this->count == 0) {
                    $this->clearTimer();
                    $this->isFinish = true;
                    $this->calltime = microtime(true) - $this->calltime;
                    if (count($this->return) == 1) {
                        $return = $this->return[0];
                    } else {
                        $return = $this->return;
                    }
                    call_user_func_array($callback, array('response' => $return, 'error' => null, 'calltime' => $this->calltime));
                    $cli->close();
                }
            }
        });

        $this->isFinish = false;
        $this->client->connect($this->ip, $this->port, $this->timeout, 1);
    }

    private function clearTimer()
    {
        if ($this->timeId) {
            swoole_timer_clear($this->timeId);
        }
    }
}