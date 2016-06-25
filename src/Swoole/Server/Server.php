<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/7/9
 * Time: 下午6:23
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Swoole\Server;

use FastD\Swoole\Handler\Handle;
use FastD\Swoole\Handler\HandlerAbstract;
use swoole_server;

/**
 * Class Server
 *
 * @package FastD\Swoole\Server
 */
abstract class Server implements ServerInterface
{
    /**
     * @var \swoole_server
     */
    protected $server;

    /**
     * @var array
     */
    protected $handles = [];

    /**
     * @var string
     */
    protected $workspace_dir;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var int
     */
    protected $mode;

    /**
     * @var int
     */
    protected $sock;

    /**
     * Swoole server run configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Server constructor.
     * @param $host
     * @param $port
     * @param int $mode
     * @param int $sock_type
     */
    final public function __construct($host = '127.0.0.1', $port = '9527', $mode = SWOOLE_BASE, $sock_type = SWOOLE_SOCK_TCP)
    {
        $this->workspace_dir = isset($_SERVER['PWD']) ? $_SERVER['PWD'] : realpath('.');

        $this->host = $host;
        $this->port = $port;
        $this->mode = $mode;
        $this->sock = $sock_type;

        $this->bootstrap();
    }

    /**
     * Bootstrap server.
     *
     * @return $this
     */
    public function bootstrap()
    {
        $this->handle(new Handle());

        $this->config = $this->configure();

        $this->server = $this->initServer();

        return $this;
    }

    /**
     * @return swoole_server
     */
    public function initServer()
    {
        return new swoole_server($this->host, $this->port, $this->mode, $this->sock);
    }

    /**
     * @return string
     */
    public function getProcessName()
    {
        return static::SERVER_NAME;
    }

    /**
     * @return string
     */
    public function getWorkSpace()
    {
        return $this->workspace_dir;
    }

    /**
     * @return string Return pid file.
     */
    public function getPid()
    {
        return $this->workspace_dir . '/' . $this->getProcessName() . '.pid';
    }

    /**
     * @param $host
     * @param $port
     * @param int $mode
     * @param int $sock_type
     * @return static
     */
    final public static function create($host = '127.0.0.1', $port = '9527', $mode = SWOOLE_BASE, $sock_type = SWOOLE_SOCK_TCP)
    {
        return new static($host, $port, $mode, $sock_type);
    }

    /**
     * @param      $name
     * @param      $callback
     * @return $this
     */
    public function on($name, $callback)
    {
        $this->handles[$name] = $callback;

        return $this;
    }

    /**
     * @param HandlerAbstract $handlerAbstract
     * @return mixed
     */
    public function handle(HandlerAbstract $handlerAbstract)
    {
        $handlerAbstract->scan($this);
    }

    /**
     * @return $this
     */
    public function daemonize()
    {
        $this->config['daemonize'] = true;

        return $this;
    }

    /**
     * @return void
     */
    public function start()
    {
        $this->server->set($this->config);

        foreach ($this->handles as $name => $handle) {
            $this->server->on($name, $handle);
        }

        $this->server->start();
    }

    /**
     * @return void
     */
    public function reload()
    {
        $this->server->reload();
    }

    /**
     * Shutdown server
     *
     * @return void
     */
    public function shutdown()
    {
        $this->server->shutdown();
    }

    /**
     * @return mixed
     */
    public function status()
    {
        return $this->server->stats();
    }
}