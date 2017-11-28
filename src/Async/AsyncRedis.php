<?php

namespace Orange\Async;

use Orange\Async\Pool\RedisProxy;

class AsyncRedis
{   
    protected static $timeout = 1;

    public static function setTimeout($timeout)
    {
        self::$timeout = $timeout;
    }

    /**
     * static call
     *
     * @param  method
     * @param  parameters
     * @return void
     */
    public static function __callStatic($method, $parameters)
    {   
        $pool = app('redisPool');
        $redis = new RedisProxy($pool);

        $redis->setMethod($method);
        $redis->setParameters($parameters);
        $res = (yield $redis);
        yield $res;
    }
}
