<?php
/**
 * Created by PhpStorm.
 * User: longmin
 * Date: 17/12/30
 * Time: 下午4:38
 */

$serv = new swoole_server("127.0.0.1", 9900);
$serv->on('connect', function ($serv, $fd){
    echo "Client:Connect.\n";
});
$serv->on('receive', function ($serv, $fd, $from_id, $data) {
    sleep(2);
    $serv->send($fd, $data);
    $serv->close($fd);
});
$serv->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});
$serv->start();