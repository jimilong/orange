<?php

namespace Orange\Discovery;

use Orange\Protocol\Packet;
use Orange\Protocol\AskId;
use \Swoole\Timer;
use Orange\Config\Config;

//ETCD DISCOVERY
class Discovery
{
    protected $socket = null;
    protected $offline = false;
    protected $timer = null;
    protected $host = '';
    protected $port = '';
    protected $servers = [];
    protected $request = null;
    protected $type;

    public function __construct($type)
    {
        $config = app('config')->get('app::rpc');
        $this->host = $config['ip'];
        $this->port = $config['port'];
        $this->type = $type;
    }

    public function onSocketReceive($cli, $data)
    {
        $this->request = null;
        $packet = new Packet('Common.Server.Discovery', $data);
        app('logger')->debug('服务发现响应 >> '.$packet->desc());
        $this->servers = $packet->getData();
        Manager::getInstance()->setServerMap($this->servers);
    }

    public function onConnect($cli)
    {
        $packet = new Packet('Common.Server.Discovery');
        $svrs = app('config')->get($this->type.'::discovery');
        $this->servers = [];
        $askId = AskId::create();
        $packet->setAskId($askId);
        $packet->setData($svrs);
        $cli->send($packet->getStream());
        app('logger')->debug('服务发现请求 >> '.$packet->desc());
        $this->request = $askId;
        Timer::after(1500, function () {
            //请求超时
            if ($this->request) {
                Manager::getInstance()->setServerMap([]);
            }
        });
        $this->timer = Timer::tick(3000, function () use ($cli, $packet, $svrs) {
            $this->servers = [];
            $askId = AskId::create();
            $packet->setAskId($askId);
            $packet->setData($svrs);
            $cli->send($packet->getStream());
            app('logger')->debug('服务发现请求 >> '.$packet->desc());
            $this->request = $askId;
            Timer::after(1500, function () {
                //请求超时
                if ($this->request) {
                    Manager::getInstance()->setServerMap([]);
                }
            });
        });
    }

    public function onError($cli)
    {
        Manager::getInstance()->setServerMap([]);
        if ($this->timer != null) {
            Timer::clear($this->timer);
            $this->timer = null;
        }

        $this->offline = true;
        unset($this->socket);
        $this->socket = null;
        Timer::after(1000, function () {
            $this->connect();
        });
    }

    public function onClose($cli)
    {
        Manager::getInstance()->setServerMap([]);
        if ($this->timer != null) {
            Timer::clear($this->timer);
            $this->timer = null;
        }

        $this->offline = true;
        unset($this->socket);
        $this->socket = null;
        Timer::after(1000, function () {
            $this->connect();
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

    public function connect()
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
        $this->socket->on('receive', [$this, 'onSocketReceive']);
        $this->socket->on('error', [$this, 'onError']);
        $this->socket->on('close', [$this, 'onClose']);
        $this->socket->connect($this->host, $this->port);
    }
}