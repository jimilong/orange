<?php

//ETCD DISCOVERY
class Discovery
{
    protected $socket = null;
    protected $online = false;
    protected $timer = null;
    protected $host = null;
    protected $port = null;
    protected $servers = [];

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function run()
    {
        $isUnixDomain = strpos($this->host, '.sock') ? true : false;
        if ($isUnixDomain) {
            $this->port = 0;
        }

        $this->socket = new \Swoole\Client($isUnixDomain ? SWOOLE_SOCK_UNIX_STREAM : SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->socket->set([
            'open_length_check'     => true,
            'package_length_type'   => 'n',
            'package_length_offset' => 0,       //第N个字节是包长度的值
            'package_body_offset'   => 2,       //第几个字节开始计算长度
            'package_max_length'    => 1024 * 8  //协议最大长度 8K
        ]);
        $this->socket->on('connect', [$this, 'onConnect']);
        $this->socket->on('receive', [$this, 'onReceive']);
        $this->socket->on('error', [$this, 'onError']);
        $this->socket->on('close', [$this, 'onClose']);
        $this->socket->connect($this->host, $this->port);
    }

    public function onReceive($cli, $data)
    {
        //TODO $this->servers = [];
        SocketKeep::getInstance()->setServerMap($this->servers);
    }

    public function onConnect($cli)
    {
        $this->online = true;
        //TODO
        $this->timer = \Swoole\Timer::tick(3000, function () use ($cli, $packet, $svrs) {
            $this->servers = [];
            //todo
            \Swoole\Timer::after(1500, function () {
                SocketKeep::getInstance()->setServerMap([]);//请求超时
            });
        });
    }

    public function onError($cli)
    {
        SocketKeep::getInstance()->setServerMap([]);
        $this->online = false;
        if ($this->timer != null) {
            \Swoole\Timer::clear($this->timer);
            $this->timer = null;
        }

        $this->socket = null;
        \Swoole\Timer::after(1000, function () {
            $this->run();
        });
    }

    public function onClose($cli)
    {
        $this->online = false;
        SocketKeep::getInstance()->setServerMap([]);
        if ($this->timer != null) {
            \Swoole\Timer::clear($this->timer);
            $this->timer = null;
        }

        $this->socket = null;
        \Swoole\Timer::after(1000, function () {
            $this->run();
        });
    }

    public function send($data)
    {
        $this->socket && $this->socket->send($data);
    }

    public function close()
    {
        $this->socket && $this->socket->close(true);
    }
}