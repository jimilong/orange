<?php

namespace Orange\Async\Pool;

use Orange\Async\Client\Base;

class MysqlProxy implements Base
{
    protected $calltime;

    protected $sql;

    protected $pool;

    protected $method;

    public function __construct($pool)
    {   
        $this->pool = $pool;
    }

    public function getConnection()
    {
        
    }

    public function query($sql)
    {
        $this->sql = $sql;
        $this->method = __FUNCTION__;
    }

    public function begin()
    {
        $this->sql = '';
        $this->method = __FUNCTION__;
    }

    public function commit()
    {
        $this->sql = '';
        $this->method = __FUNCTION__;
    }

    public function rollback()
    {
        $this->sql = '';
        $this->method = __FUNCTION__;
    }

    public function execute(callable $callback, $task)
    {
        $this->pool->request($this->method, $this->sql, $callback, $task->getTaskId());
    }
}
