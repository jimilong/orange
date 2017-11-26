<?php

namespace Orange\Protocol;

class Packet
{
    const FLAG_RESPONSE = 1; //是否是返回协议
    const FLAG_EVENT = 2; //是否是事件协
    const FLAG_EVENT_PUBLISH = 2; //事件发布协议
    const FLAG_EVENT_SUBSCRIBE = 4; //事件订阅协议
    const HEADER_LEN = 18;

    public $name = '';
    public $len = 0; //2  包的总长度
    public $flag = 0;//2
    public $service = 0;//4
    public $time = 0; //4
    public $askId = 0;//4
    public $code = 0;//2
    public $bodyStream = null;

    public function __construct($service = '', $stream = '')
    {
        if ($service) {
            $this->service = crc32($service);
            $this->name = $service;
        }
        if ($stream) {
            $data = unpack('nlen/nflag/Nservice/Ntime/NaskId/ncode', $stream);
            $this->len = $data['len'];
            $this->service = $data['service'];
            $this->flag = $data['flag'];
            $this->code = $data['code'];
            $this->time = $data['time'];
            $this->askId = $data['askId'];
            $this->bodyStream = substr($stream, static::HEADER_LEN);
        }
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getService()
    {
        return $this->service;
    }

    public function setAskId($askId)
    {
        return $this->askId = $askId;
    }

    public function getAskId()
    {
        return $this->askId;
    }

    public function setFlag($flag)
    {
        $this->flag = $flag;
    }

    public function getFlag()
    {
        return $this->flag;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setData($data)
    {
        $this->bodyStream = json_encode($data);
    }

    public function getData()
    {
        return json_decode($this->bodyStream, true);
    }

    public function getStream()
    {
        //nlen/nflag/Nservice/Ntime/NaskId/ncode
        $this->len = strlen($this->bodyStream) + self::HEADER_LEN - 2;
        $this->time = time();
        return pack(
            'nnNNNn',
            $this->len,
            $this->flag,
            $this->service,
            $this->time,
            $this->askId,
            $this->code
        ) . $this->bodyStream;
    }

    public function __clone()
    {
        $this->bodyStream = null;
    }

    public function desc()
    {
        return sprintf(
            'name = %s , service=%s, len=%s, flag=%d, askId=%s, code=%d, data=%s',
            $this->name,
            $this->service,
            $this->len,
            $this->flag,
            $this->askId,
            $this->code,
            $this->bodyStream
        );
    }
}
