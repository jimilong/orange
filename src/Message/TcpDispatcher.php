<?php

namespace Orange\Message;

use Orange\Protocol\Packet;

class TcpDispatcher
{
    protected $accepts = [];

    public function addAcceptHandler($handler = null)
    {
        if (!class_exists($handler)) {
            throw new \Exception('handler can`t be load>' . $handler, 1);
        } else {
            $handler = $this->getHandlerObject($handler);
            $this->accepts[$handler->id] = $handler;
        }
    }

    protected function getHandlerObject($handler)
    {
        //addAcceptHandler
        $reflection = new \ReflectionClass($handler);
        $doc = $reflection->getDocComment();
        $docs = explode(PHP_EOL, $doc);
        $name = '';
        $service = '';
        $protocol = '';
        foreach ($docs as $line) {
            $line = trim($line);
            if (strpos($line, '* @service ') === 0) {
                $service = trim(explode('* @service ', $line)[1]);
            } elseif (strpos($line, '* @protocol ') === 0) {
                $protocol = trim(explode('* @protocol ', $line)[1]);
            } elseif (strpos($line, '* @name ') === 0) {
                $name = trim(explode('* @name ', $line)[1]);
            }
        }

        if (!$service || !$protocol) {
            throw new \Exception($handler . ' Server config fail!. please check DocComment', 1);
        }

        $object = new \StdClass;
        $object->name = $name;
        $object->protocol = $protocol;
        $object->service = $service;
        $object->id = crc32($service);
        $object->handler = $handler;
        app('logger')->debug('协议处理', [
            'name' => $name, 'service' => $service, 'protocol' => $protocol,
            'id' => $object->id,
        ]);

        return $object;
    }

    public function dispatch(Packet $packet, Connection $conn)
    {
        if (isset($this->accepts[$packet->getService()])) {
            $packet->setName($this->accepts[$packet->getService()]->service);
            app('logger')->debug('协议开始', [$packet->desc()]);
            $handler = $this->accepts[$packet->getService()];
            $packet->name = $handler->service;
            $class = $handler->handler;
            $class = new $class($packet, $conn);

            yield $class();
        } else {
            //todo
            yield false;
        }
    }
}