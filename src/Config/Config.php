<?php
/**
 * Created by PhpStorm.
 * User: longmin
 * Date: 17/8/12
 * Time: 下午8:18
 */
namespace Orange\Config;

class Config
{
    private static $instance;

    protected $config = [];

    /**
     * 获取config下得值
     *
     * @param  configName,  name::key
     * @return string
     */
    public function get($configName)
    {
        return  self::getInstance()->read($configName);
    }

    /**
     * 设置config下得值
     *
     * @param  key
     * @param  subKey
     * @param  value
     */
    public function set($key, $subKey, $value)
    {
        self::getInstance()->setCustom($key, $subKey, $value);
    }

    /**
     * read config
     *
     * @param  configName,  name::key
     * @return array
     */
    public function read($configName)
    {
        $configName = explode('::', $configName);

        $return = [];
        if (count($configName) == 2) {
            if (isset($this->config[$configName[0]][$configName[1]])) {
                $return = $this->config[$configName[0]][$configName[1]];
            }
        }

        return $return;

    }

    /**
     * 设置config
     *
     * @param  array config
     */
    public function setConfig($config)
    {
        $this->config = array_merge($this->config, $config);
    }

    public function setCustom($key, $subKey, $value)
    {
        $this->config[$key][$subKey] = $value;
    }

    /**
     * 获取config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * return single class
     *
     * @return \Orange\Config\Config
     */
    public static function getInstance(){

        if (!(self::$instance instanceof self)){
            self::$instance = new self;
        }

        return self::$instance;
    }

     /**
     * @param $file
     *
     * @return array
     */
    public function load($file)
    {
        $info = pathinfo($file);

        $filename = $info['filename'];
        $extension = $info['extension'];
        if (empty($filename) || empty($extension)) {
            return;
        }
        switch ($extension) {
            case 'ini':
                $config = parse_ini_file($file, true);
                break;
            case 'json':
                $config = json_decode(file_get_contents($file), true);
                break;
            case 'php':
            default:
                $config = include $file;
        }

        $this->config = array_merge($this->config, [$filename => $config]);
    }
}