<?php

namespace Orange\Container;

use Orange\Config\Config;
use Orange\Http\Request;
use Orange\Http\Response;

class Containerbak
{   
    protected $instances;

    protected $request;

    protected $response;

    protected $timezone;

    protected $environment;

    protected $appRoot;

    protected $locale;

    protected $debug = false;

    protected $rpcData = null;

    public function __construct()
    {
        $this->setTimezone();

        $this->setEnvironment();

        $this->setLocale();

        $this->needDebug();
    }

    /**
     *  向App存储一个单例对象
     *
     * @param  name，callable
     * @return object
     */
    public function singleton($name, $callable = null)
    {
        if (!isset($this->instances[$name]) && $callable) {
            $this->instances[$name] = call_user_func($callable);
        }

        return isset($this->instances[$name]) ? $this->instances[$name] : null;
    }

    /**
     * 设置时区
     *
     */
    public function setTimezone()
    {
        $this->timezone = app('config')->get('app::timezone');
        date_default_timezone_set($this->getTimezone());
    }


    /**
     * 获取当前时区
     *
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * 获取当前环境
     *
     *@return string prod｜dev
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * 设置环境
     *
     */
    public function setEnvironment()
    {
        $this->environment = app('config')->get('app::environment');
    }

    /**
     * 设置系统根目录
     *
     */
    public function setAppRoot()
    {
        $this->appRoot = app('config')->get('app::root');
    }

    /**
     * 获取系统根目录
     *
     *@return string
     */
    public function getAppRoot()
    {
        return $this->appRoot;
    }

    /**
     * 设置地区
     *
     */
    public function setLocale()
    {
        $this->locale = app('config')->get('app::locale');
    }

    /**
     * 获取设置的地区
     *
     *@return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    private function needDebug()
    {
        if (app('config')->get('app::environment') == "dev" && app('config')->get('app::debug')) {
            $this->debug = true;
        }
    }

    public function isDebug()
    {
        return $this->debug;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    //RPC data
    public function setRpcData($data)
    {
        $this->rpcData = $data;
    }

    public function getRpcData()
    {
        return $this->rpcData;
    }
}
