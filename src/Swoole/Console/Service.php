<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/5/21
 * Time: 下午8:29
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Swoole\Console;

use FastD\Swoole\Server\Server;

/**
 * Service 管理脚本
 *
 * Class Service
 *
 * @package FastD\Swoole\Console
 */
class Service
{
    /**
     * @var static
     */
    protected static $service;

    /**
     * @var Server
     */
    protected $server;

    /**
     * Service constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server= $server;
    }

    /**
     * @return void
     */
    public function start()
    {
        try {
            $this->server->start();
        } catch (\Exception $e) {
            Output::output(sprintf('Server[%s] host[%s] port[%s] is already in use', $this->server->getPid(), $this->server->getHost(), $this->server->getPort()));
        }
    }

    /**
     * @return void
     */
    public function shutdown()
    {
        $pid = $this->server->getPid();

        posix_kill($pid, SIGTERM);
    }

    /**
     * @return void
     */
    public function reload()
    {
        $pid = $this->server->getPid();

        posix_kill($pid, SIGUSR1);
    }

    /**
     * @return void
     */
    public function status()
    {
        $status = $this->server->status();

        print_r($status);
    }

    /**
     * @param Server $server
     * @return static
     */
    public static function server(Server $server)
    {
        if (null === static::$service) {
            static::$service = new static($server);
        }

        return static::$service;
    }
}