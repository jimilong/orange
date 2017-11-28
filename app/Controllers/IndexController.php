<?php
/**
 * Created by PhpStorm.
 * User: longmin
 * Date: 17/8/23
 * Time: 下午10:56
 */
namespace App\Controllers;

use Orange\Async\AsyncFile;
use Orange\Async\AsyncMysql;
use Orange\Async\AsyncRedis;
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

        yield app('asyncLog')->debug('async log test!!');

        try {
            yield AsyncMysql::begin();
            $r = (yield AsyncMysql::query('update ad set title = "666" where id = 65'));
            //$r = (yield AsyncRedis::hGet('HASH:U:INFO:12345678', 'nickname'));
            var_dump($r);
            yield AsyncMysql::rollback();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }



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