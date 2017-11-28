<?php

namespace Orange\Async\Client;

use Orange\Coroutine\Task;
use Orange\Application\Code;

class File implements Base
{
    protected $filename;

    protected $content;

    protected $action;

    protected $flags = 0;

    public function __construct() {}

    public function read($filename)
    {
        $this->filename = $filename;
        $this->action = __FUNCTION__;
    }

    public function write($filename, $content, $flags)
    {
        $this->filename = $filename;
        $this->content = $content;
        $this->flags = $flags;
        $this->action = __FUNCTION__;
    }

    public function execute(callable $callback, $task)
    {
        $f = true;
        switch ($this->action) {
            case 'read':
                $f = swoole_async_readfile($this->filename, function($filename, $content) use ($callback) {
                    call_user_func_array($callback, [$content]);
                });
                break;
            case 'write':
                $f = swoole_async_writefile($this->filename, $this->content, function($filename) use ($callback) {
                    call_user_func_array($callback, [true]);
                }, $this->flags);
                break;
            default:
                break;
        }
        if ($f == false) {
            $e = new \Exception('open file '.$this->filename.' failed', code::OPEN_FILE_FAILED);
            call_user_func_array($callback, [false, $e]);
            //yield throwException($e);
            //throw new \Exception('open file '.$this->filename.' failed', 101);
        }
    }
}
