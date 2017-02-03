<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

include __DIR__ . '/../../vendor/autoload.php';

/**
 * Class DemoServer
 */
class DemoServer extends \FastD\Swoole\Server\Tcp
{
    public function doWork(swoole_server $server, $fd, $data, $from_id)
    {
        echo $data . PHP_EOL;
        return 'hello tcp';
    }
}

return DemoServer::createServer('tcp swoole', 'tcp://0.0.0.0:9527', [
    'pid_file' => '/tmp/swoole.pid',
]);