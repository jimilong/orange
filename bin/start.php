<?php

define('__ROOT__', __DIR__ . '/../');

include __ROOT__ . 'vendor/autoload.php';

use Orange\Coroutine\Task;
use Orange\Async\AsyncFile;
use Orange\Config\Config;
use Orange\Async\AsyncLog;
use Orange\Async\AsyncMysql;
use Orange\Async\AsyncTcp;
use Orange\Server\HttpServer;
use Orange\Protocol\Packet;
use Orange\Promise\Promise;

function echoTimes($msg, $max) {
    for ($i = 1; $i <= $max; ++$i) {
        echo "$msg iteration $i\n";
        yield;

    }
}

function task() {
    (yield echoTimes('foo', 10)); // print foo ten times
    //echo "---\n";
    //(yield echoTimes('bar', 5)); // print bar five times
}

function writeFile() {
//    $sql = 'select * from `user`';
//    $res = (yield AsyncMysql::query($sql, false));
//    $a = $res->getResult();
//    var_dump($a);
//    yield AsyncLog::alert('log test', ['a' => 'b']);
//    echo 'bbb'.PHP_EOL;
    for ($i=0;$i<5;$i++) {
        try {
            $file = '/Users/longmin/web/test.txt';
            yield AsyncFile::write($file, "\n new test!!", FILE_APPEND);
            //$content = (yield AsyncFile::read($file));
            echo 'write file  -> '.$i.PHP_EOL;
            //var_dump($content);
        } catch (\Exception $e) {
            //var_dump($e->getMessage());
        }
    }
}

function querySql() {
    $sql = 'select * from `user`';
    $res = (yield AsyncMysql::query($sql, false));
    if ($res) {
        $a = $res->getResult();
        var_dump($a);
    } else {
        echo 'query error'.PHP_EOL;
    }
}

function rpcSend()
{
    $svrs = ['UserSvr', 'RichSvr'];
    foreach ($svrs as $s) {
        AsyncTcp::addCall('Common.Server.Discovery', ['name' => $s]);
    }
    //并行发送数据包
    //AsyncTcp::addCall('Common.Server.Discovery', ['name' => "UserSvr"]);
    //AsyncTcp::addCall('Common.Server.Discovery', ['name' => "UserSvr"]);
    $res = (yield AsyncTcp::multiCall());

    foreach ($svrs as $key => $svr) {
        $packet = new \Orange\Protocol\Packet('', $res[$key]);
        $data[$svr] = $packet->getData();
    }

    print_r($data);


    //$res = (yield AsyncTcp::call('Common.Server.Discovery', ['name' => "UserSvr"]));
    //var_dump($res);
}

function readFile1()
{
    //for ($i=0;$i<5;$i++) {
        try {
            $file = '/Users/longmin/web/test.txt';
            $resp = (yield AsyncFile::read($file));
            var_dump($resp);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }

    //}
}

//$container = new \Orange\Container\Container();

//Task::execute(rpcSend(), 0, $container);

//Task::execute(Manager::getInstance()->discovery());


//Task::execute(querySql(), 0, $container);
//Task::execute(readFile1(), 0, $container);
//Task::execute(task(), 0, $container);
//Task::execute(writeFile(), 0, $container);
//Task::execute(task1(), 0, $container);

function task3() {
    echo 'do task3'.PHP_EOL;
    $e = new \Exception('test yield error', 11110);
    yield throwException($e);
    yield $a = 1 + 2;
}

function task2() {
    echo 'do task2'.PHP_EOL;
    $a = (yield task3());
    echo 'task3-resp:'.$a.PHP_EOL;
    //task3();
}

function task1() {
    echo 'do task1'.PHP_EOL;
    try {
        yield task2();
    } catch (\Exception $e) {
        var_dump($e->getMessage());
    }
}


function test_promise()
{
    $promise = new Promise(function ($resolve, $reject) {
        swoole_timer_after(2000, $resolve);
    });

    $promise->then(function ($value) {

        yield 2222;
        echo "1111".PHP_EOL;
        //return $value;
    });
}




//test_promise();

function test_yield()
{
    $a = (yield test_promise());

    echo '333'.PHP_EOL;
    var_dump($a);
}

Task::execute(test_yield());




//////********************//////////

//define('APP_NAME', 'orange_http');
//
//$options = getopt("s:");
//$options = empty($options) ? '' : $options['s'];
//
//$file = __ROOT__ . 'config/http.php';
//$config = Config::getInstance();
//$config->load($file);
//$pidFile = $config->get('http::setting')['pid_file'];
//
//$pid = null;
//if(file_exists($pidFile)){
//    $pid = file_get_contents($pidFile);
//}
//
//if($pid && $options){
//    switch($options){
//        //reload worker
//        case 'reload':
//            exec('kill -USR1 '.$pid);
//            echo "reload success ! \n";
//            break;
//        case 'stop':
//            //kill -SIGTERM is doesn't work
//            exec('kill -TERM '.$pid);
//            echo "stop service ! \n";
//            break;
//        case 'start':
//            $app = \Orange\Application\Application::instance();
//            $app->run('http');
//            echo "start service ! \n";
//            break;
//        default:
//            echo "No no such pid file \n";
//    }
//}else {
//    if ($options == 'start') {
//        $app = \Orange\Application\Application::instance();
//        $app->run('http');
//    }
//}