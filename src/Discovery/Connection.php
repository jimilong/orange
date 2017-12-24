<?php

namespace Orange\Discovery;

use Orange\Protocol\Packet;
use Orange\Protocol\AskId;
use Orange\Async\Client\Base;
use Orange\Coroutine\Task;
use \Swoole\Timer;

class Connection implements Base
{
    protected $socket = null;
    protected $offline = false;
    protected $timer = null;
    protected $host = '';
    protected $port = '';
    protected $isFinish = false;
    protected $calltime = null;
    protected $callback = null;
    protected $name = null;

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

    //RPC异步调用
    public function execute(callable $callback, $task)
    {
        $packet = $task->getContext()->getData('rpcData');//
        $askId = $packet->getAskId();
        $timeout = 1000;
        $this->calltime[$askId] = microtime(true);
        $this->callback[$askId] = $callback;
        $this->name[$askId] = $packet->getService();
        $this->isFinish[$askId] = false;
        $this->send($packet->getStream());
        app('syncLog')->debug('异步RPC调用开始 >> '.$packet->desc());
        $task->getContext()->setData('rpcData', null);//
        Timer::after($timeout, function () use ($askId, $callback) {
            if (isset($this->isFinish[$askId]) && !$this->isFinish[$askId]) {
                unset($this->isFinish[$askId]);
                unset($this->callback[$askId]);
                unset($this->calltime[$askId]);
                unset($this->name[$askId]);
                $e = new \Exception('rpc timeout', 503);
                call_user_func_array($callback, [false, $e]);
            }
        });
    }

    public function onSocketReceive($cli, $data)
    {
        $packet = new Packet('', $data);
        $askId = $packet->getAskId();
        if (isset($this->callback[$askId]) && ($packet->getFlag() == 1)) {
            unset($this->isFinish[$askId]);
            if (isset($this->name[$askId])) {
                $packet->setName($this->name[$askId]);
            }
            $return = $packet->getData();
            call_user_func_array($this->callback[$askId], [$return]);
            app('syncLog')->debug('异步RPC调用响应 >> '.$packet->desc());
            unset($this->calltime[$askId]);
            unset($this->callback[$askId]);
            unset($this->name[$askId]);
        } else {
            if ($packet->getService() == crc32('Common.Server.Keeplive')) {
                $packet->setName('Common.Server.Keeplive');
                app('syncLog')->debug('服务心跳响应 >> '.$packet->desc());
            }
        }
    }

    public function onConnect($cli)
    {
        $this->timer = Timer::tick(3000, function () use ($cli) {
            $packet = new Packet('Common.Server.Keeplive');
            $packet->setAskId(AskId::create());
            $packet->setData(['ts' => microtime(true)]);
            $cli->send($packet->getStream());
            app('syncLog')->debug('与服务保持心跳开始 >> '.$packet->desc());
        });
    }

    public function onError($cli)
    {
        if ($this->timer != null) {
            Timer::clear($this->timer);
            $this->timer = null;
        }
        $this->offline = true;
        $this->socket = null;
        $this->isFinish = false;
        $this->calltime = null;
        $this->callback = null;
    }

    public function onClose($cli)
    {
        if ($this->timer != null) {
            Timer::clear($this->timer);
            $this->timer = null;
        }
        $this->offline = true;
        $this->socket = null;
        $this->isFinish = false;
        $this->calltime = null;
        $this->callback = null;
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
        $this->socket->connect($this->host, $this->port);
    }

    public function isOnline()
    {
        return !$this->offline;
    }
}