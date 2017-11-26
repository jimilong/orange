<?php
/**
 * Created by PhpStorm.
 * User: longmin
 * Date: 17/8/23
 * Time: 下午10:58
 */

return [
    'router' => [
        '/index/index' => [
            'controller' => 'App\Controllers\IndexController',
            'action'     => 'index',
            'method'     => ['get', 'post']
        ]
    ]

];