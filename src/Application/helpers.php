<?php

use Orange\Application\Application;
use Orange\Coroutine\SysCall;
use Orange\Coroutine\Task;
use Orange\Coroutine\Scheduler;
use Orange\Coroutine\Signal;
use Orange\Promise\Promise;
use Orange\Promise\Race;
use Orange\Exception\TimeoutException;

/**
 * Get the available container instance.
 *
 * @param  string  $abstract
 * @return mixed|\Group\App\App
 */
function app($abstract = null)
{
    if (is_null($abstract)) {
        return Application::getInstance();
    }

    return Application::getInstance()->get($abstract);
}

function killTask()
{
    return new SysCall(function (Task $task) {
        return Signal::TASK_KILLED;
    });
}

function taskSleep($ms)
{
    return new SysCall(function (Task $task) use ($ms) {
        \Swoole\Timer::after($ms, function () use ($task) {
            $task->send(null);
            $task->run();
        });

        return Signal::TASK_SLEEP;
    });
}

function getTaskId() {
    return new SysCall(function(Task $task){
        $task->send($task->getTaskId());

        return Signal::TASK_CONTINUE;
    });
}

function getContext() {
    return new SysCall(function(Task $task){
        $task->send($task->getContext());

        return Signal::TASK_CONTINUE;
    });
}

function throwException($e) {
    return new SysCall(function(Task $task) use ($e){
        $task->sendException($e);

        return Signal::TASK_CONTINUE;
    });
}


function timeout($ms, $ec = TimeoutException::class)
{
    $racer = Promise::deferred();
    if ($ms > 0) {
        \Swoole\Timer::after($ms, static function () use ($racer, $ec) {
            $racer->reject(new $ec);
        });
    }
    return $racer;
}


//function msleep(int $ms, Closure $do = null)
//{
//    return new Promise(function (Promise $promised) use ($ms, $do) {
//        Timer::after($ms, static function () use ($promised, $do) {
//            $promised->resolve($do ? $do() : null);
//        });
//    });
//}

//function await(Closure $program, int $ms = 60000, string $ec = TimeoutException::class)
//{
//    $race =  new Race([new Promise(function ($resolve, $reject) use ($program) {
//            //todo
//             }), timeout($ms, $ec)]);
//
//}
