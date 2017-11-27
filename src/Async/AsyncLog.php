<?php

namespace Orange\Async;

class AsyncLog
{
    protected $logDir = null;
    public function __construct($path)
    {
        $this->logDir = $path;
    }

    public function debug($message, array $context  = [], $model = 'web.app')
    {
        return $this->writeLog(__FUNCTION__, $message, $context, $model);
    }

    public function info($message, array $context  = [], $model = 'web.app')
    {
        return $this->writeLog(__FUNCTION__, $message, $context, $model);
    }

    public function notice($message, array $context  = [], $model = 'web.app')
    {
        return $this->writeLog(__FUNCTION__, $message, $context, $model);
    }

    public function warning($message, array $context  = [], $model = 'web.app')
    {
        return $this->writeLog(__FUNCTION__, $message, $context, $model);
    }

    public function error($message, array $context  = [], $model = 'web.app')
    {
        return $this->writeLog(__FUNCTION__, $message, $context, $model);
    }

    public function critical($message, array $context  = [], $model = 'web.app')
    {
        return $this->writeLog(__FUNCTION__, $message, $context, $model);
    }

    public function alert($message, array $context  = [], $model = 'web.app')
    {
        return $this->writeLog(__FUNCTION__, $message, $context, $model);
    }

    public function emergency($message, array $context  = [], $model = 'web.app')
    {
        return $this->writeLog(__FUNCTION__, $message, $context, $model);
    }

    public function writeLog($level, $message, $context, $model)
    {   
        if (!empty($context)) {
            $context = json_encode($context);
        } else {
            $context = "";
        }

        $record = "[".date('Y-n-d H:i:s')."] {$model}.{$level}: {$message} [{$context}]\n";
        yield AsyncFile::write($this->logDir. 'async-' .date('Y-m-d').".log", $record, FILE_APPEND);
    }
}
