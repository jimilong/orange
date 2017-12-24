<?php

namespace Orange\Server;

use Orange\Coroutine\Context;
use Orange\Coroutine\Task;
use Orange\Protocol\Packet;
use Orange\Message\Connection;
use \Swoole\Http\Request;
use \Swoole\Http\Response;

class Tcp extends ServerAbstract
{
    public function onReceive($serv, $fd, $from_id, $data)
    {
        $packet = new Packet('', $data);
        if ($packet->getService() == crc32('Common.Server.Keeplive')) {
            $packet->setName('Common.Server.Keeplive');
            $packet->setFlag(1);
            $packet->setData([]);
            $serv->send($fd, $packet->getStream());
        } else {
            $conn = new Connection($serv, $fd);
            //$this->dispatcher->dispatch($packet, $conn);
            $context = new Context();
            $task = new Task($this->app->handleTcpAccept($packet, $conn), 0, $context);
            $task->run();
        }
    }

    public function onConnect($serv, $fd)
    {
        $fdinfo = $serv->connection_info($fd);
        app('syncLog')->info('新客户端连接 >> '.$fdinfo['remote_ip'].':'.$fdinfo['remote_port']);
    }

    public function start()
    {
        $this->server->on('receive', [$this, 'onReceive']);
        $this->server->on('connect', [$this, 'onConnect']);
        $this->server->start();
    }
}

