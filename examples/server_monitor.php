<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

include __DIR__ . '/../vendor/autoload.php';

use FastD\Swoole\Server\Server;

class DemoServer extends Server
{
    /**
     * @param \swoole_server $server
     * @param int $fd
     * @param int $from_id
     * @param string $data
     * @return mixed
     */
    public function doWork(\swoole_server $server, int $fd, int $from_id, string $data)
    {
        $server->send($fd, 'hello ' . $from_id);
        $server->close($fd);
    }
}

DemoServer::run([
    'monitors' => [
        [
            'host' => '127.0.0.1',
            'port' => '9883',
            'sock' => SWOOLE_SOCK_TCP
        ]
    ]
]);

/**
 *
 * $server = new DemoServer();
 * $server->monitoring([
 * [
 *      'host' => '127.0.0.1',
 *      'port' => '9883',
 *      'sock' => SWOOLE_SOCK_TCP
 * ]
 * ]);
 * $server->start();
 */

