<?php

namespace Orange\ServiceProvider;

use Orange\Container\ServiceProviderInterface;
use Orange\Container\Container;
use Orange\Config\Config;

class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the service provider.
     *
     * @return object
     */
    public function register(Container $container)
    {
        $config = Config::getInstance();

        $configDir = __ROOT__ . 'config/';
        $files = scandir($configDir);
        if (!empty($files)) {
            foreach ($files as $file) {
                $config->load($configDir.$file);
            }
        }

        $container->add('config', $config);
    }

    public function getName()
    {
        return 'config';
    }
}