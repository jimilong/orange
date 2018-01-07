<?php

namespace Orange\Async\Client;

use Orange\Coroutine\Task;
use Orange\Application\Code;
use Orange\Promise\Promise;
use Orange\Promise\Race;

class PromiseFile
{
    protected $filename;

    protected $content;

    protected $action;

    protected $timeout = 1000; //ms

    protected $flags = 0;

    public function read($filename)
    {
        $this->filename = $filename;
        $this->action = __FUNCTION__;

        return $this->execute();
    }

    public function write($filename, $content, $flags)
    {
        $this->filename = $filename;
        $this->content = $content;
        $this->flags = $flags;
        $this->action = __FUNCTION__;

        return $this->execute();
    }

    public function execute()
    {
        $promise = Promise::deferred();
        $f = true;
        switch ($this->action) {
            case 'read':
                $f = swoole_async_readfile($this->filename, function($filename, $content) use ($promise) {
                    $promise->resolve($content);
                });
                break;
            case 'write':
                $f = swoole_async_writefile($this->filename, $this->content, function($filename) use ($promise) {
                    $promise->resolve(true);
                }, $this->flags);
                break;
            default:
                break;
        }
        if ($f == false) {
            $e = new \Exception('open file '.$this->filename.' failed', code::OPEN_FILE_FAILED);
            $promise->reject($e);
        }

        return Promise::race([$promise, timeout($this->timeout)]);
    }
}
