<?php

namespace Orange\Async;

use Orange\Config\Config;

class AsyncLog
{
    public static function debug($message, array $context  = [], $model = 'web.app')
    {
        return self::writeLog(__FUNCTION__, $message, $context, $model);
    }

    public static function info($message, array $context  = [], $model = 'web.app')
    {
        return self::writeLog(__FUNCTION__, $message, $context, $model);
    }

    public static function notice($message, array $context  = [], $model = 'web.app')
    {
        return self::writeLog(__FUNCTION__, $message, $context, $model);
    }

    public static function warning($message, array $context  = [], $model = 'web.app')
    {
        return self::writeLog(__FUNCTION__, $message, $context, $model);
    }

    public static function error($message, array $context  = [], $model = 'web.app')
    {
        return self::writeLog(__FUNCTION__, $message, $context, $model);
    }

    public static function critical($message, array $context  = [], $model = 'web.app')
    {
        return self::writeLog(__FUNCTION__, $message, $context, $model);
    }

    public static function alert($message, array $context  = [], $model = 'web.app')
    {
        return self::writeLog(__FUNCTION__, $message, $context, $model);
    }

    public static function emergency($message, array $context  = [], $model = 'web.app')
    {
        return self::writeLog(__FUNCTION__, $message, $context, $model);
    }

    public static function writeLog($level, $message, $context, $model)
    {   
        $logDir = app('config')->get("app::log");
        if (!empty($context)) {
            $context = json_encode($context);
        } else {
            $context = "";
        }

        $record = "[".date('Y-n-d H:i:s')."] {$model}.{$level}: {$message} [{$context}]\n";
        yield AsyncFile::write($logDir.date('Ymd').".log", $record, FILE_APPEND);
    }
}
