<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/1/18
 * Time: 下午9:47
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

include __DIR__ . '/../vendor/autoload.php';

use FastD\Swoole\Http\HttpServer;
use FastD\Swoole\Request;
use FastD\Swoole\Http\HttpRequest;

/**
 * Class Http
 */
class Http extends HttpServer
{
    /**
     * @param \FastD\Http\SwooleServerRequest $request
     * @return \FastD\Http\Response
     */
    public function doRequest(\FastD\Http\SwooleServerRequest $request)
    {
        return $this->html('hello http');
    }
}

Http::run([
    'host' => '0.0.0.0',
    'ports' => [
        [
            'host' => '0.0.0.0',
            'port' => '9988',
            'sock' => SWOOLE_SOCK_TCP,
            'config' => [], // 重写端口配置,
            'callback' => 'class name'
        ],
    ]
]);
