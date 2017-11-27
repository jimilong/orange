<?php
return [
    'onWorkStartServices' => [
        'Orange\ServiceProvider\ConfigServiceProvider',
        'Orange\ServiceProvider\LoggerServiceProvider',
        'Orange\ServiceProvider\RouterServiceProvider',
        'Orange\ServiceProvider\SyncLogServiceProvider',
        'Orange\ServiceProvider\AsyncLogServiceProvider',
        //'Orange\ServiceProvider\TcpDispatcherServiceProvider',
        'Orange\ServiceProvider\MysqlPoolServiceProvider',
        //'Orange\ServiceProvider\RedisPoolServiceProvider',
    ],
    'setting' => [
        'ip' => '127.0.0.1',
        'port' => 9502,
        //日志
        'daemonize' => true,
        'log_file' => __ROOT__.'runtime/http/error.log',
        'pid_file' => __ROOT__.'runtime/http/server.pid',
        'worker_num' => 2,    //worker process num
        'backlog' => 128,   //listen backlog
        'heartbeat_idle_time' => 30,
        'heartbeat_check_interval' => 10,
        'dispatch_mode' => 1,
        'max_request' => 10000,
        'discard_timeout_request' => true
    ],
    'discovery' => [],//['UserSvr', 'RichSvr', 'SsoSvr'],
];
