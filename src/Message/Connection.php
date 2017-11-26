<?php

namespace Orange\Message;

use Orange\Protocol\Packet;

class Connection
{
    protected $serv;
    protected $fd;
    protected $data;

    public function __construct($serv, $fd)
    {
        $this->serv = $serv;
        $this->fd = $fd;
    }

    public function setData(Packet $packet)
    {
        $this->data = $packet->getStream();
    }

    public function send()
    {
        $this->serv->send($this->fd, $this->data);
    }
}