<?php

class SocketEntity
{
    private $name = null;//protocol
    private $ip = null;
    private $port = null;
    private $socket = null;
    private $online = false;
    private $heartTime = 3000;//毫秒
    private $timer = null;//心跳定时器

    public function __construct($host = '127.0.0.1', $port = 0)
    {
        $this->host = $host;
        $this->port = $port;
        $isUnixDomain = strpos($host, '.sock') ? true : false;
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
        $this->socket->on('receive', [$this, 'onSocketReceive']);
        $this->socket->on('error', [$this, 'onError']);
        $this->socket->on('close', [$this, 'onClose']);
    }

    protected function onConnect($cli)
    {
        $this->timer = \Swoole\Timer::tick($this->heartTime, function () use ($cli) {
            //TODO 维持心跳
        });
        $this->online = true;
    }

    protected function onSocketReceive($cli, $data)
    {
        //TODO 维持心跳 转发响应数据
    }

    protected function onError($cli)
    {
        $this->online = false;
        if ($this->timer != null) {
            \Swoole\Timer::clear($this->timer);
            $this->timer = null;
        }
        $this->socket = null;
    }

    protected function onClose($cli)
    {
        $this->online = false;
        if ($this->timer != null) {
            \Swoole\Timer::clear($this->timer);
            $this->timer = null;
        }
        $this->socket = null;
    }

    public function run()
    {
        $this->socket->connect($this->host, $this->port);
    }

    public function send($data)
    {
        $this->socket && $this->socket->send($data);
    }

    public function close()
    {
        $this->socket && $this->socket->close(true);
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIP()
    {
        return $this->ip;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getAddr()
    {
        return $this->ip.':'.$this->port;
    }

    public function isOnline()
    {
        return $this->online;
    }
}