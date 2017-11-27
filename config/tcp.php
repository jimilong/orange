<?php

return [
    'info' => [
        'name'  => 'UserSvr',
        'ip' => '127.0.0.1',
        'port' => 9888,
        'methods' => [], //提供的服务
    ],
    'onWorkStartServices' => [
        'Orange\ServiceProvider\ConfigServiceProvider',
        'Orange\ServiceProvider\LoggerServiceProvider',
        'Orange\ServiceProvider\TcpDispatcherServiceProvider',
        'Orange\ServiceProvider\SyncLogServiceProvider',
        'Orange\ServiceProvider\AsyncLogServiceProvider',
        //'Orange\Async\Pool\MysqlPoolServiceProvider',
        //'Orange\Async\Pool\RedisPoolServiceProvider',
    ],
    'setting' => [
        'ip' => '127.0.0.1',
        'port' => 9888,
        //日志
        'daemonize' => true,
        'log_file' => __ROOT__.'runtime/tcp/error.log',
        'pid_file' => __ROOT__.'runtime/tcp/server.pid',
        'worker_num' => 2,    //worker process num
        'backlog' => 128,   //listen backlog
        'heartbeat_idle_time' => 30,
        'heartbeat_check_interval' => 10,
        'dispatch_mode' => 1,
        'max_request' => 10000,
        'discard_timeout_request' => true
    ],
    'rpcHandler' => [
        App\services\User\Info::class,
    ],
    'discovery' => [],//['RichSvr', 'SsoSvr'],
];