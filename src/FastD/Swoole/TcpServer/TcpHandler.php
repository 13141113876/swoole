<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/7/11
 * Time: 下午9:28
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Swoole\TcpServer;

use FastD\Swoole\Server\ServerHandlerAbstract;

class TcpHandler extends ServerHandlerAbstract
{
    public function __construct()
    {
        parent::__construct([
            'receive',
            'connect',
            'close',
            'start',
            'workerStart',
            'managerStart',
            'workerStop',
        ]);
    }

    public function onReceive(\swoole_server $server, $fd, $from_id, $data)
    {
        echo 'receive' . PHP_EOL;
        $server->send($fd, $data);
        $server->close($fd);
    }

    public function onConnect(\swoole_server $server, $fd, $from_id)
    {
        echo 'connection' . PHP_EOL;
    }

    public function onClose(\swoole_server $server, $fd, $from_id)
    {
        echo 'close' . PHP_EOL;
    }
}