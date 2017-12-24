<?php

namespace Orange\Discovery;

use Orange\Protocol\Packet;
use Orange\Protocol\AskId;

class Rpc
{
    public static function call($service, array $data)
    {
        $packet = new Packet($service);
        $packet->setData($data);
        $packet->setAskId(AskId::create());
        $context = (yield getContext());
        $context->setData('rpcData', $packet);

        $conn = Manager::getInstance()->getConnection($service);

        $res = (yield $conn);
        yield $res;
    }
}