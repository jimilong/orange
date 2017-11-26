<?php

namespace Orange\ServiceProvider;

use Orange\Container\ServiceProviderInterface;
use Orange\Container\Container;
use Orange\Log\Logger;
use Orange\Log\AccessHandler;
use Monolog\Formatter\JsonFormatter;
use Orange\Config\Config;

class LoggerServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the service provider.
     *
     * @return object
     */
    public function register(Container $container)
    {
        $path = app('config')->get('app::log');
        $name = APP_NAME;//app('config')->get('app::name');
        $logger = new Logger($name);
        $stream_handler = new AccessHandler($path.'/'.$name.'.log', Logger::DEBUG);
        $stream_handler->setFormatter(new JsonFormatter());
        $logger->pushHandler($stream_handler);

        $container->add('logger', $logger);
    }

    public function getName()
    {
        return 'logger';
    }
}