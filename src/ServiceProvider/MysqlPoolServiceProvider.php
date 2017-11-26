<?php

namespace Orange\ServiceProvider;

use Orange\Container\ServiceProviderInterface;
use Orange\Container\Container;
use Orange\Async\Pool\MysqlPool;

class MysqlPoolServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the service provider.
     *
     * @return object
     */
    public function register(Container $container)
    {
        $container->add('mysqlPool', new MysqlPool());
    }

    public function getName()
    {
        return 'mysqlPool';
    }
}