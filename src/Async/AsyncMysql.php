<?php

namespace Orange\Async;

use Orange\Async\Pool\MysqlProxy;

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
        yield $res;
    }

    public static function begin()
    {
        $pool = app('mysqlPool');
        $mysql = new MysqlProxy($pool);

        $mysql->begin();
        $res = (yield $mysql);
        yield $res;
    }

    public static function commit()
    {
        $pool = app('mysqlPool');
        $mysql = new MysqlProxy($pool);

        $mysql->commit();
        $res = (yield $mysql);
        yield $res;
    }

    public static function rollback()
    {
        $pool = app('mysqlPool');
        $mysql = new MysqlProxy($pool);

        $mysql->rollback();
        $res = (yield $mysql);
        yield $res;
    }
}
