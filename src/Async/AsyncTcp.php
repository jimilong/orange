<?php

namespace Orange\Async;

use Orange\Protocol\Packet;
use Orange\Async\Client\Tcp as TcpClient;

class AsyncTcp
{
    protected static $data = [];

    public static function call($service, $data)
    {
        $packet = new Packet($service);
        $packet->setData($data);
        $stream = $packet->getStream();
        $res = (yield self::request($stream, 1));
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

    public static function request($data, $count)
    {
        $client = new TcpClient();
        $client->setCount($count);
        $client->setData($data);
        $res = (yield $client);

        if ($res && $res['response']) {
            yield $res['response'];
        } else {
            yield false;
        }
    }
}