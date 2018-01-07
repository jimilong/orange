<?php

namespace Orange\Async\Client;

use Orange\Promise\Promise;

class TcpPromise
{
    protected $ip;

    protected $port;

    protected $data;

    protected $timeout; //ms

    protected $client;

    public function __construct($ip, $port, $timeout = 2000)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->client->set([
            'open_length_check'     => true,
            'package_length_type'   => 'n',
            'package_length_offset' => 0,       //第N个字节是包长度的值
            'package_body_offset'   => 2,       //第几个字节开始计算长度
            'package_max_length'    => 1024 * 8  //协议最大长度 8K
        ]);
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function parse($data)
    {
        return $data;
    }

    public function execute()
    {
        $promise = Promise::deferred();
        $this->client->on("connect", function ($cli) use ($promise) {
            $cli->send($this->data);
        });

        $this->client->on('close', function ($cli) {
        });

        $this->client->on('error', function ($cli) use ($promise) {
            $e = new \Exception(socket_strerror($cli->errCode), $cli->errCode);
            $promise->reject($e);
        });

        $this->client->on("receive", function ($cli, $data) use ($promise) {
            $data = $this->parse($data);//todo
            $promise->resolve($data);
            $cli->close();
        });
        $this->client->connect($this->ip, $this->port, $this->timeout, 1);

        return Promise::race([$promise, timeout($this->timeout)]);
    }
}