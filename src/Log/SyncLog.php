<?php

namespace Orange\Log;

class Log
{
    const TRACE = 'TRACE'; //流程追踪
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARN = 'WARN';
    const ERROR = 'ERROR';
    const ALERT = 'ALERT';
    const RECORD = 'RECORD';

    protected $logPath = null;

    public function __construct($path)
    {
        $this->logPath = $path;
    }

    /**
     *
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->write(static::ALERT, $message, $context);
    }

    /**
     *
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->write(static::ERROR, $message, $context);
    }

    /**
     * 出现非错误性的异常。
     *
     *
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function warn($message, array $context = array())
    {
        $this->write(static::WARN, $message, $context);
    }

    /**
     * 流程追踪。
     *
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function trace($message, array $context = array())
    {
        $this->write(static::TRACE, $message, $context);
    }

    /**
     * 重要事件
     *
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->write(static::INFO, $message, $context);
    }

    /**
     * debug 详情
     *
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $this->write(static::DEBUG, $message, $context);
    }

    /**
     * 记录日志 详情
     *
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function record($message, array $context = array())
    {
        $this->write(static::DEBUG, $message, $context);
    }

    /**
     * 任意等级的日志记录
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function write($level, $message, array $context = [])
    {
        if (is_object($message)) {
            $message = json_decode(json_encode($message), true);
        }

        if (is_array($message)) {
            $message = json_encode($message, 256);
        }

        foreach ($context as $key => $val) {
            $message = str_replace('{' . $key . '}', $val, $message);
        }

        $trace = debug_backtrace();

        $caller = isset($trace[1]) ? $trace[1] : [];

        if (isset($caller['file'])) {
            $file = pathinfo($caller['file'], PATHINFO_BASENAME);
            $line = $caller['line'];
        } else {
            $file = $line = '';
        }

        $this->printConsoleLog($message, $level, $file, $line);
    }

    public function printConsoleLog($message, $level = 'TRACE', $file = '', $line = 0)
    {
        $message = sprintf('[%s][%s][%s][%s]%s', date('Y-m-d H:i:s'), $level, $file, $line, $message);

        error_log($message . PHP_EOL, 3, $this->logPath . 'sync-' .date('Y-m-d') . '.log');
    }
}
