<?php

namespace Orange\Async;

use Orange\Async\Pool\RedisProxy;
use Orange\Application\Code;

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
        if ($res && $res['response']) {
            yield $res['response'];
        } else {
            $e = new \Exception($res['error'], Code::ASYNC_REDIS_COMMAND);
            yield throwException($e);
            //yield false;
        }
    }
}
