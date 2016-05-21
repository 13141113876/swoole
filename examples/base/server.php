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

include __DIR__ . '/../../vendor/autoload.php';

use FastD\Swoole\Server\TcpServer;
use FastD\Swoole\Console\Service;

$server = TcpServer::create();

$server->on('receive', function (\swoole_server $server, $fd) {
    echo 'receive' . PHP_EOL;
    $server->close($fd);
});

Service::server($server)->start();