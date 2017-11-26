<?php
/**
 * Created by PhpStorm.
 * User: longmin
 * Date: 17/8/23
 * Time: 下午10:56
 */
namespace App\Controllers;

use Orange\Async\AsyncFile;
use Orange\Container\Container;
use Orange\Discovery\Rpc;
use Psr\Http\Message\ServerRequestInterface;
use Orange\Http\SwooleResponse;

class IndexController
{
    protected $request;
    protected $response;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
        $this->response = new SwooleResponse();
    }

    public function index()
    {
        //$call = (yield Rpc::call('UserSvr.Info.Get', ['uid' => 111111]));
        $call = 'test-debug';
        echo 'CALL-RESP----index-index:'.PHP_EOL;
        var_dump($call);

        $this->response->withContent(json_encode(['resp' => $call]))->withContentType('application/json; charset=utf-8');
        yield $this->response;


        //$resp = 'rpc-result:'.json_encode($call);
        //$this->response->setContent($resp);


        //$file = '/tmp/test11/async_zan/test.txt';
        //$file = '/Users/longmin/web/test.txt';
        //$res = file_get_contents($file);
        //$res = (yield AsyncFile::read($file));
        //yield \Orange\Async\AsyncLog::info($res);
        //$this->response->setContent($res);

//     return 'index';
//        $file = '/Users/longmin/web/test.txt';
//        $content = (yield AsyncFile::read($file));
//        yield $content;
    }
}