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
use Orange\Promise\All;
use Orange\Promise\Race;
use Orange\Async\AsyncPromiseFile;
use Orange\Async\AsyncTcpPromise;

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
    try {
        $file = '/Users/longmin/web/test.txt';
        $resp = (yield AsyncPromiseFile::read($file));
        var_dump($resp);
    } catch (\Exception $e) {
        var_dump('error', $e->getMessage());
    }
}

function testTcp()
{
    try {
        $resp = (yield AsyncTcpPromise::call('user.info', ['uid' => '111', 'nickname' => 'longmsdu']));
        var_dump($resp);
    } catch (\Exception $e) {
        var_dump('tcp error', $e->getMessage());
    }
}

Task::execute(testTcp());

//function p($str) {
//    echo $str.PHP_EOL;
//    yield throwException(new \Exception('test error', 100));
//}
//
//function t($str) {
//    yield p($str);
//}
//
//function gen() {
//    try {
//        yield t('hello');
//        yield t('world');
//    }
//    catch (\Exception $e) {
//        echo $e->getMessage().PHP_EOL;
//    }
//    //yield t('test');
//    //yield t('end');
//}




//$container = new \Orange\Container\Container();

//Task::execute(rpcSend(), 0, $container);

//Task::execute(Manager::getInstance()->discovery());


//Task::execute(querySql(), 0, $container);
//Task::execute(readFile1(), 0, $container);
//Task::execute(task(), 0, $container);
//Task::execute(writeFile(), 0, $container);
//Task::execute(task1(), 0, $container);


function testOut() {
    $promise1=  new Promise(function ($resolve, $reject) {
        swoole_timer_after(2000, function () use ($resolve) {
            $resolve(1);
        });
    });
//
//    $promise2 = new Promise(function ($resolve, $reject) {
//        swoole_timer_after(1000, function () use ($reject) {
//            $reject(2);
//        });
//    });

    $race = Promise::race([$promise1, timeout(20)]);

//    $race->then(function ($value) {
//        var_dump('y', $value);
//    })->eCatch(function ($e) {
//        var_dump('n', $e);
//    });


    return $race;
}

function testYield() {
    try {
        $res = (yield testOut());
        var_dump('done', $res);
    } catch (\Exception $e) {
        var_dump('e', $e->getMessage());
    }

}

//Task::execute(testYield());



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