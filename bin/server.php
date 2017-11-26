<?php

define('__ROOT__', __DIR__ . '/../');

include __ROOT__ . 'vendor/autoload.php';

use Orange\Config\Config;

//use Orange\Server\TcpServer;
//use Orange\Message\Observer;
//use Orange\Message\TcpDispatcher;
//
//$observer = new Observer();
//$observer->addAcceptHandler(App\services\User\Info::class);
//$dispatcher = new TcpDispatcher();
//$dispatcher->addObserver($observer);
//
//$server = new TcpServer('127.0.0.1', 9888);
//$server->setDispatcher($dispatcher);
//
//$server->start();

define('APP_NAME', 'orange_tcp');

$options = getopt("s:");
$options = empty($options) ? '' : $options['s'];

$file = __ROOT__ . 'config/tcp.php';
$config = Config::getInstance();
$config->load($file);
$pidFile = $config->get('tcp::setting')['pid_file'];

$pid = null;
if(file_exists($pidFile)){
    $pid = file_get_contents($pidFile);
}

if($pid && $options){
    switch($options){
        //reload worker
        case 'reload':
            exec('kill -USR1 '.$pid);
            echo "reload success ! \n";
            break;
        case 'stop':
            //kill -SIGTERM is doesn't work
            exec('kill -TERM '.$pid);
            echo "stop service ! \n";
            break;
        case 'start':
            $app = \Orange\Application\Application::instance();
            $app->run('tcp');
            echo "start service ! \n";
            break;
        default:
            echo "No no such pid file \n";
    }
}else {
    if ($options == 'start') {
        $app = \Orange\Application\Application::instance();
        $app->run('tcp');
    }
}