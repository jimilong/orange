<?php
namespace Orange\Discovery;

use Orange\Application\Singleton;
use Orange\Async\AsyncTcp;
use Orange\Protocol\Packet;
use Orange\Config\Config;

class Manager
{
    use Singleton;

    protected $connMap = [];   //ip:port => conn
    protected $serverMap = [];  // server => [ip:port,ip:port]

    public function setServerMap($servers)
    {
        $this->serverMap = $servers;
    }

    public function discovery($type) //tcp http
    {
        $discovery = new Discovery($type);
        $discovery->connect();

        \Swoole\Timer::tick(5000, function() {
            app('logger')->debug('刷新服务', $this->serverMap);
            $this->createConnection();
        });
    }

    public function createConnection()
    {
        $activeMap = array_keys($this->connMap);
        foreach ($this->serverMap as $svr => $dests) {
            $diff = array_diff($activeMap, $dests);
            foreach ($dests as $dest) {
                if (in_array($dest, $activeMap) && $this->connMap[$dest]->isOnline()) {
                    continue;
                }
                $p = explode(':', $dest);
                $this->connMap[$dest] = new Connection($p[0], $p[1]);
                $this->connMap[$dest]->connect();
            }

            foreach ($diff as $dest) {
                if (!isset($this->connMap[$dest])) {
                    continue;
                }
                unset($this->connMap[$dest]);
                if ($this->connMap[$dest]->isOnline()) {
                    $this->connMap[$dest]->close();
                }
            }
        }
    }

    public function getConnection($method)
    {
        $svr = explode('.', $method)[0];
        if (isset($this->serverMap[$svr])) {
            $j = count($this->serverMap[$svr]);
            //todo Load Balance Strategy
            for ($i=0;$i<$j;$i++) {
                $ip_port = $this->serverMap[$svr][$i];
                $conn = $this->connMap[$ip_port];
                if ($conn->isOnline()) {
                    return $conn;
                }
            }
        }

        return null;
    }
}