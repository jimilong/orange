<?php

namespace Orange\Async;

use Orange\Async\Pool\MysqlProxy;
use Orange\Application\Code;

class AsyncMysql
{   
    protected static $timeout = 1;

    protected static $userPool = true;

    public static function setTimeout($timeout)
    {
        self::$timeout = $timeout;
    }

    public static function query($sql)
    {
        $pool = app('mysqlPool');
        $mysql = new MysqlProxy($pool);

        $mysql->query($sql);
        $res = (yield $mysql);
        if ($res && $res['response']) {
            yield $res['response'];
        } else {
            $e = new \Exception($res['error'], Code::ASYNC_MYSQL_QUERY);
            yield throwException($e);
            //yield false;
        }
    }

    public static function begin()
    {
        $pool = app('mysqlPool');
        $mysql = new MysqlProxy($pool);

        $mysql->begin();
        $res = (yield $mysql);
        if ($res && $res['response']) {
            yield $res['response'];
        } else {
            $e = new \Exception($res['error'], Code::ASYNC_MYSQL_BEGIN);
            yield throwException($e);
            //yield false;
        }
    }

    public static function commit()
    {
        $pool = app('mysqlPool');
        $mysql = new MysqlProxy($pool);

        $mysql->commit();
        $res = (yield $mysql);
        if ($res && $res['response']) {
            yield $res['response'];
        } else {
            $e = new \Exception($res['error'], Code::ASYNC_MYSQL_COMMIT);
            yield throwException($e);
            //yield false;
        }
    }

    public static function rollback()
    {
        $pool = app('mysqlPool');
        $mysql = new MysqlProxy($pool);

        $mysql->rollback();
        $res = (yield $mysql);
        if ($res && $res['response']) {
            yield $res['response'];
        } else {
            $e = new \Exception($res['error'], Code::ASYNC_MYSQL_ROLLBACK);
            yield throwException($e);
            //yield false;
        }
    }
}
