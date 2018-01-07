<?php

namespace Orange\Async;

use Orange\Protocol\Packet;
use Orange\Async\Client\TcpPromise as TcpClient;

class AsyncTcpPromise
{
    protected static $data = [];

    public static function call($service, $data)
    {
        $packet = new Packet($service);
        $packet->setData($data);
        $stream = $packet->getStream();
        $res = (yield self::request($stream));
        yield $res;
    }

    public static function addCall($service, $data)
    {
        $packet = new Packet($service);
        $packet->setData($data);
        $stream = $packet->getStream();

        self::$data[] = $stream;
    }

    public static function multiCall()
    {
        $res = (yield self::request(implode('', self::$data), count(self::$data)));
        self::$data = [];
        yield $res;
    }

    public static function request($data)
    {
        $client = new TcpClient('127.0.0.1', 9900);
        $client->setData($data);
        $p = $client->execute();
        $res = (yield $p);

        yield $res;
    }
}