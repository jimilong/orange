<?php

use Orange\Application\Application;
use Orange\Coroutine\SysCall;
use Orange\Coroutine\Task;
use Orange\Coroutine\Scheduler;
use Orange\Coroutine\Signal;

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