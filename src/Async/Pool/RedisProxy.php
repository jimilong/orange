<?php

namespace Orange\Async\Pool;

use Orange\Async\Client\Base;

class RedisProxy implements Base
{
    protected $parameters;

    protected $pool;

    protected $method;

    public function __construct($pool)
    {   
        $this->pool = $pool;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function execute(callable $callback, $task)
    {   
        $this->pool->request($this->method, $this->parameters, $callback);
    }
}
