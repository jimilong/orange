<?php

namespace Orange\ServiceProvider;

use Orange\Container\ServiceProviderInterface;
use Orange\Container\Container;
use Orange\Async\AsyncLog;

class AsyncLogServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the service provider.
     *
     * @return object
     */
    public function register(Container $container)
    {
        $path = app('config')->get('app::log');
        $name = APP_NAME;
        $asyncLog = new AsyncLog($path.$name.'/');

        $container->add('asyncLog', $asyncLog);
    }

    public function getName()
    {
        return 'asyncLog';
    }
}