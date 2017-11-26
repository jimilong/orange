<?php

namespace Orange\Router;

use Orange\Config\Config;

class Router
{
    protected $pathInfo = [];
    protected $path = [];

    private static $instance;

    public function __construct()
    {
        $this->pathInfo = app('config')->get('router::router');
        $this->path = array_keys($this->pathInfo);
    }

    public static function Match($uri, $method)
    {
        return self::getInstance()->dispatch($uri, $method);
    }

    public static function getInstance()
    {
        if (!(self::$instance instanceof self)){
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function dispatch($uri, $method)
    {
        $result = [];
        if (in_array($uri, $this->path)) {
            $pathInfo = $this->pathInfo[$uri];
            if (in_array(strtolower($method), $pathInfo['method'])) {
                $result = ['controller' => $pathInfo['controller'], 'action' => $pathInfo['action']];
            }
        }

        return $result;
    }
}