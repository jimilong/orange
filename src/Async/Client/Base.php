<?php
/**
 * Created by PhpStorm.
 * User: longmin
 * Date: 17/8/11
 * Time: 下午4:58
 */
namespace Orange\Async\Client;

interface Base
{
    public function execute(callable $callback, $task);
}