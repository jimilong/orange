<?php
return [
    //默认可以不开启读写配置，读写配置可以配置多个
    'pdo' => [
        'default' => [

            //"driver" => "pdo_mysql",

            "host" => "127.0.0.1",

            "port" => "3306",

            "dbname" => "db_test",

            "user" => "root",

            "password" => "",

            "charset" => "utf8mb4",
        ],
        //mysql连接池数量
        'maxPool' => 10,

        //mysql连接池数量
        'minPool' => 2,

        //mysql连接超时时间
        'timeout' => 2,
    ],

    //redis null
    'cache' => 'redis',

    'redis' => [

        //redis连接池数量
        'maxPool' => 5,

        'minPool' => 2,

        //redis连接超时时间
        'timeout' => 5,
    
        'default' => [
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'prefix'   => '',
            'auth'     => '',
            'connect'  => 'persistence'
        ],
    ],
];
