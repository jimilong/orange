<?php

namespace Orange\Server;

use Orange\Coroutine\Context;
use Orange\Coroutine\Task;
use \Swoole\Http\Request;
use \Swoole\Http\Response;

class Http extends ServerAbstract
{
    public function onRequest(Request $request, Response $response)
    {
        $s=memory_get_usage(); //获取当前占用内存
        if ($request->server['request_uri'] == '/favicon.ico') {
            $response->end();
        } else {
            $context = new Context();
            $task = new Task($this->app->handleHttpAccept($request, $response), 0, $context);
            $task->run();
            unset($context);
            unset($task);
        }

        unset($request);
        unset($response);
        $m=memory_get_usage(); //获取当前占用内存
        $n=memory_get_usage(); //获取当前占用内存

        echo $s.PHP_EOL;
        echo $m.PHP_EOL;
        echo $n.PHP_EOL;
        echo ($m - $n).PHP_EOL;
    }

    public function start()
    {
        $this->server->on('Request', [$this, 'onRequest']);
        $this->server->start();
    }
}

