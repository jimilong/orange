<?php

class SocketKeep
{
    private $activeMap = [];  //ip:port => entity  活跃链接
    private $queueMap = [];   //name => queue => [ip:port]
    private $serverMap = [];  //[serv => [ip:port,ip:port]]
    private $refreshTime = 5000; //毫秒

    private static $instance = null;

    final public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function setServerMap($servers)
    {
        $this->serverMap = $servers;
    }

    public function keepalive()
    {
        //发现服务
        $host = '';
        $port = '';
        $discovery = new Discovery($host, $port);
        $discovery->run();

        //刷新服务
        \Swoole\Timer::tick($this->refreshTime, function() {
            $this->createSocket();
        });
    }

    public function put(SocketEntity $socket)
    {
        $this->activeMap[$socket->getAddr()] = $socket;
        if (!isset($this->queueMap[$socket->getName()])) {
            $queue = new SplQueue();
            $this->queueMap[$socket->getName()] = $queue;
        }

        $this->queueMap[$socket->getName()]->enqueue($socket->getAddr());
    }

    public function release(SocketEntity $socket)
    {
        if (!isset($this->queueMap[$socket->getName()])) {
            $queue = new SplQueue();
            $this->queueMap[$socket->getName()] = $queue;
        }

        $this->queueMap[$socket->getName()]->enqueue($socket->getAddr());
    }

    public function getSocket($name)
    {
        if (isset($this->queueMap[$name])) {
            if (!$this->queueMap[$name]->isEmpty()) {
                $addr = $this->queueMap[$name]->dequeue();
                if (isset($this->activeMap[$addr])) {
                    return $this->activeMap[$addr];
                }
            }
        }

        return false;
    }

    public function createSocket()
    {
        $activeServ = array_keys($this->activeMap);
        foreach ($this->serverMap as $svr => $dests) {
            $diff = array_diff($activeServ, $dests);
            foreach ($dests as $dest) {
                if (in_array($dest, $activeServ) && $this->activeMap[$dest]->isOnline()) {
                    continue;
                }
                $p = explode(':', $dest);
                $socket = new SocketEntity($p[0], $p[1]);
                $socket->setName($svr)->run();//todo name

                $this->activeMap[$dest] = $socket;
                $this->put($socket);
            }

            foreach ($diff as $dest) {
                if (!isset($this->activeMap[$dest])) {
                    continue;
                }
                if ($this->activeMap[$dest]->isOnline()) {
                    $this->activeMap[$dest]->close();
                }
                unset($this->activeMap[$dest]);
            }
        }
    }
}