<?php
/**
 * Created by PhpStorm.
 * User: longmin
 * Date: 17/11/26
 * Time: 下午1:22
 */
namespace Orange\Application;

class Code
{
    const OPEN_FILE_FAILED = 1000;
    const FILE_NO_ACCESS = 1001;
    const ASYNC_MYSQL_QUERY = 1002;
    const ASYNC_MYSQL_BEGIN = 1003;
    const ASYNC_MYSQL_COMMIT = 1004;
    const ASYNC_MYSQL_ROLLBACK = 1005;
    const ASYNC_REDIS_COMMAND = 1006;
}