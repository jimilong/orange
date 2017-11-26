<?php
return [
    'name' => 'orange',

    // prod|dev
    'environment' => 'dev',

    //只有在dev环境下才生效。tip: swoole http server下无法正常debug
    'debug' => true,

    //zh|en|fr...
    'locale' => 'zh',

    //时区
    'timezone' => 'Asia/Shanghai',

    'log' => __ROOT__.'runtime/',

    'rpc' => [
        'ip' => '127.0.0.1',
        'port' => 9501,
    ],
];
