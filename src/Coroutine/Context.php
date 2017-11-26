<?php

namespace Orange\Coroutine;

class Context
{
    protected $data = null;

    public function setData($k, $data)
    {
        $this->data[$k] = $data;
    }

    public function getData($k)
    {
        return $this->data[$k];
    }
}