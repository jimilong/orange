<?php

namespace Orange\ServiceProvider;

use Orange\Container\ServiceProviderInterface;
use Orange\Container\Container;
use Orange\Log\SyncLog;

class SyncLogServiceProvider implements ServiceProviderInterface
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
        $syncLog = new SyncLog($path.$name.'/');

        $container->add('syncLog', $syncLog);
    }

    public function getName()
    {
        return 'syncLog';
    }
}