<?php

namespace Orange\Async;

use Orange\Async\Pool\MysqlProxy;
use Orange\Async\Client\Mysql;

class AsyncMysql
{   
    protected static $timeout = 1;

    protected static $userPool = true;

    public static function setTimeout($timeout)
    {
        self::$timeout = $timeout;
    }

    public static function query($sql, $userPool = true)
    {   
        if ($userPool) {
            $pool = app('mysqlPool');
            $mysql = new MysqlProxy($pool);
        } else {
            $container = (yield getContainer());
            $timeout = self::$timeout;
            $mysql = $container->singleton('mysql', function() use ($timeout) {
                $mysql = new Mysql();
                $mysql->setTimeout($timeout);
                return $mysql;
            });
        }

        $mysql->query($sql);
        $res = (yield $mysql);
        if ($res && $res['response']) {
            yield $res['response'];
        } else {
            yield false;
        }
    }

    public static function begin($userPool = true)
    {
        if (!$userPool) {
            $res = (yield self::query('begin', false));
            yield $res;
            return;
        }

        $pool = app('mysqlPool');
        $mysql = new MysqlProxy($pool);

        $mysql->begin();
        $res = (yield $mysql);
        if ($res && $res['response']) {
            yield $res['response'];
        } else {
            yield false;
        }
    }

    public static function commit($userPool = true)
    {
        if (!$userPool) {
            $res = (yield self::query('commit', false));
            yield $res;
            return;
        }

        $pool = app('mysqlPool');
        $mysql = new MysqlProxy($pool);

        $mysql->commit();
        $res = (yield $mysql);
        if ($res && $res['response']) {
            yield $res['response'];
        } else {
            yield false;
        }
    }

    public static function rollback($userPool = true)
    {
        if (!$userPool) {
            $res = (yield self::query('rollback', false));
            yield $res;
            return;
        }

        $pool = app('mysqlPool');
        $mysql = new MysqlProxy($pool);

        $mysql->rollback();
        $res = (yield $mysql);
        if ($res && $res['response']) {
            yield $res['response'];
        } else {
            yield false;
        }
    }
}
