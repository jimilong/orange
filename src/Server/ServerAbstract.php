<?php

namespace Orange\Server;

use Orange\Discovery\Manager;
use swoole_http_server;
use swoole_server;

Abstract class ServerAbstract
{
    protected $server;

    protected $app;

    protected $type;

    public function __construct($app, $options, $type = 'http')
    {
        $this->app = $app;
        $this->type = $type;
        $class = $type == 'http' ? 'swoole_http_server' : 'swoole_server';
        $host = $options['ip'];
        $port = $options['port'];
        $this->server = new $class($host, $port);
        unset($options['ip']);
        unset($options['port']);
        $this->server->set($options);

        $this->server->on('Start', [$this, 'onStart']);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->server->on('Shutdown', [$this, 'onShutdown']);
    }

    public function onStart()
    {
        if (PHP_OS !== 'Darwin') {
            swoole_set_process_name('php '.$this->type.' server: master');
        }
    }

    public function onWorkerStart($serv, $workerId)
    {
        if (PHP_OS !== 'Darwin') {
            swoole_set_process_name('php '.$this->type.' server: worker');
        }
        $this->app->bootstrap();//启动初始化
        if (!empty(app('config')->get($this->type.'::discovery'))) {
            Manager::getInstance()->discovery($this->type);
        }
    }

    public function onWorkerStop($serv, $workerId)
    {
        $this->app->releasePool();
    }

    public function onShutdown($serv)
    {
        //
    }

    abstract function start();
}