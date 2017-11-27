<?php

namespace Orange\Application;

use ErrorException;
use Orange\Config\Config;
use Orange\Http\SwooleRequest;
use Orange\Http\SwooleResponse;
use Orange\Router\Router;
use Orange\Container\Container;
use Orange\Container\ServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Orange\Server\Http;
use Orange\Server\Tcp;

/**
 * Class Application.
 */
class Application extends Container
{
    use Singleton;

    /**
     * The Orange version.
     *
     * @const string
     */
    const VERSION = '0.1.0';

    protected $server;

    protected $type;

    protected $config;

    /**
     * AppKernel constructor.
     *
     * @param $path
     */
    public function __construct()
    {
        //$this->bootstrap();
    }

    public function run($type)
    {
        $this->type = $type;
        $file = __ROOT__ . 'config/'.$type.'.php';
        $this->config = Config::getInstance();
        $this->config->load($file);
        $options = $this->config->get($type.'::setting');
        switch ($type) {
            case 'http':
                $this->server = new Http($this, $options, 'http');
                break;
            case 'tcp' :
                $this->server = new Tcp($this, $options, 'tcp');
                break;
        }
        $this->server->start();
    }

    public function bootstrap()
    {
        $services = $this->config->get($this->type.'::onWorkStartServices');
        $this->registerExceptionHandler();
        $this->registerServicesProviders($services);
    }

    /**
     * @param ServiceProviderInterface[] $services
     */
    protected function registerServicesProviders(array $services)
    {
        foreach ($services as $service) {
            $this->register(new $service());
        }
    }

    protected function registerExceptionHandler()
    {
        error_reporting(-1);

        set_exception_handler([$this, 'handleException']);

        set_error_handler(function ($level, $message, $file = '', $line = 0, $context = []) {
            throw new ErrorException($message, 0, $level, $file, $line);
        });
    }

    public function handleException($e)
    {
        //todo
    }

    //处理Http请求
    public function handleHttpAccept($request, $response)
    {
        try {
            $req = SwooleRequest::createServerRequestFromSwoole($request);
            $resp = (yield $this->doRequest($req));
            foreach ($resp->getHeaders() as $key => $header) {
                $response->header($key, $resp->getHeaderLine($key));
            }
            foreach ($resp->getCookieParams() as $key => $cookieParam) {
                $response->cookie($key, $cookieParam);
            }

            $response->status($resp->getStatusCode());
            $response->end((string) $resp->getBody());
        } catch (\Throwable $t) {
            $e = t2ex($t);
            yield throwException($e);
        } catch (\Exception $e) {
            yield throwException($e);
        } finally {
            unset($req);
            unset($resp);
            unset($request);
            unset($response);

            while (ob_get_level() > 0) {
                ob_end_flush();
            }
        }
    }

    public function doRequest(ServerRequestInterface $request)
    {
        $router = $this->get('router')->dispatch($request->getUri()->getPath(), $request->getMethod());
        //$router = Router::Match($request->getUri()->getPath(), $request->getMethod());
        if (empty($router)) {
            $response = new SwooleResponse();
            $response->withStatus(404)
                ->withContent('This is crazy, but this page was not found!');
        } else {
            $controller = new $router['controller']($request);
            $response = (yield call_user_func_array([$controller, $router['action']], []));
        }

        yield $response;
    }

    public function handleTcpAccept($packet, $conn)
    {
        yield app('tcpDispatcher')->dispatch($packet, $conn);
    }

    public function releasePool()
    {
        $this->offsetUnset('mysqlPool');
        $this->offsetUnset('redisPool');
    }
}
