<?php

namespace Orange\ServiceProvider;

use Orange\Container\ServiceProviderInterface;
use Orange\Container\Container;
use Orange\Message\TcpDispatcher;
use Orange\Config\Config;

class TcpDispatcherServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the service provider.
     *
     * @return object
     */
    public function register(Container $container)
    {
        $handlers = app('config')->get('tcp::rpcHandler');
        $dispatcher = new TcpDispatcher();
        if (!empty($handlers)) {
            foreach ($handlers as $handler) {
                $dispatcher->addAcceptHandler($handler);
            }
        }

        $container->add('tcpDispatcher', $dispatcher);
    }
}