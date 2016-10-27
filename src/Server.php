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

namespace FastD\Swoole;

use FastD\Packet\Binary;
use FastD\Swoole\Console\Output;
use FastD\Swoole\Console\Process;
use swoole_process;
use swoole_server;

/**
 * Class Server
 *
 * @package FastD\Swoole\Server
 */
abstract class Server
{
    const SERVER_NAME = 'fds';

    /**
     * @var swoole_server
     */
    protected $swoole;

    /**
     * Swoole server run configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * @var string
     */
    protected $port = '9527';

    /**
     * @var int
     */
    protected $mode = SWOOLE_PROCESS;

    /**
     * @var int
     */
    protected $sockType = SWOOLE_SOCK_TCP;

    /**
     * @var string
     */
    protected $pid;

    /**
     * @var bool
     */
    protected $booted = false;

    /**
     * 多端口支持
     *
     * @var array
     */
    protected $ports = [];

    /**
     * @var array
     */
    protected $monitors = [];

    /**
     * @var array
     */
    protected $discoveries = [];

    /**
     * @var Server
     */
    protected static $instance;

    /**
     * Server constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->pid = realpath('.') . '/run/' . static::SERVER_NAME . '.pid';

        $this->configure($config);
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return isset($this->config['debug']) ? $this->config['debug'] : false;
    }

    /**
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * Bootstrap server.
     *
     * @return $this
     */
    public function bootstrap()
    {
        if (!$this->isBooted()) {

            $this->swoole = $this->initSwoole();

            $this->scanOnHandles();

            foreach ($this->ports as $key => $port) {
                $serverPort = $this->swoole->listen($port['host'], $port['port'], $port['sock'] ?? SWOOLE_SOCK_TCP);
                if (isset($port['config'])) {
                    $serverPort->set($port['config']);
                }
                if (isset($port['callback'])) {
                    if (!is_object($port['callback'])) {
                        $port['callback'] = new $port['callback'];
                    }

                    $serverPort->on('connect', [$port['callback'], 'onConnect']);
                    $serverPort->on('receive', [$port['callback'], 'onReceive']);
                    $serverPort->on('packet', [$port['callback'], 'onPacket']);
                    $serverPort->on('close', [$port['callback'], 'onClose']);
                }
                $this->ports[$key] = $serverPort;
            }

            if (isset($this->config['discoveries']) && !empty($this->config['discoveries'])) {
                $this->discovery($this->config['discoveries']);
                unset($this->config['discoveries']);
            }

            if (isset($this->config['monitors']) && !empty($this->config['monitors'])) {
                $this->monitoring($this->config['monitors']);
                unset($this->config['monitors']);
            }

            $this->booted = true;
        }

        return $this;
    }

    /**
     * 如果需要自定义自己的swoole服务器,重写此方法
     *
     * @return swoole_server
     */
    public function initSwoole()
    {
        return new swoole_server($this->host, $this->port, $this->mode, $this->sockType);
    }

    /**
     * @param array $config
     * @return array
     */
    public function configure(array $config)
    {
        if (isset($config['host'])) {
            $this->host = $config['host'];
            unset($config['host']);
        }
        if (isset($config['port'])) {
            $this->port = $config['port'];
            unset($config['port']);
        }
        if (isset($config['mode'])) {
            $this->mode = $config['mode'];
            unset($config['mode']);
        }
        if (isset($config['sock'])) {
            $this->sockType = $config['sock'];
            unset($config['sock']);
        }
        if (isset($config['pid'])) {
            $this->pid = $config['pid'];
            unset($config['pid']);
        }

        if (isset($config['ports'])) {
            $this->ports = $config['ports'];
            unset($config['ports']);
        }

        $this->config = $config;

        return $config;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param $sock
     * @return string
     */
    public function getServerType($sock = null)
    {
        if (null === $sock) {
            $sock = $this->sockType;
        }

        switch (get_class($this->swoole)) {
            case 'swoole_http_server':
                return 'http';
            case 'swoole_websocket_server':
                return 'ws';
            case 'swoole_server':
                return ($sock === SWOOLE_SOCK_UDP || $sock === SWOOLE_SOCK_UDP6) ? 'udp' : 'tcp';
            default:
                return 'unknown';
        }
    }

    /**
     * @return string
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        return static::SERVER_NAME;
    }

    /**
     * 服务发现
     *
     * @param array $discoveries
     * @return $this
     */
    public function discovery(array $discoveries)
    {
        $this->discoveries = $discoveries;

        foreach ($discoveries as $discovery) {
            $process = new swoole_process(function () use ($discovery) {
                while (true) {
                    sleep(1);
                    echo 'discovery ' . $discovery['host'] . PHP_EOL;
                }
            });

            $this->swoole->addProcess($process);
        }

        return $this;
    }

    /**
     * @param array $monitors
     * @return $this
     */
    public function monitoring(array $monitors)
    {
        $this->monitors = $monitors;

        $self = $this;

        foreach ($this->monitors as $monitor) {
            $process = new swoole_process(function () use ($monitor, $self) {
                $client = new Client($monitor['sock']);
                while (true) {
                    $client->connect($monitor['host'], $monitor['port']);
                    $client->send(Binary::encode([
                        'host' => $self->getHost(),
                        'port' => $self->getPort(),
                        'status' => $self->getSwooleInstance()->stats(),
                    ]));
                    sleep(20);
                }
            });

            $this->swoole->addProcess($process);
        }

        return $this;
    }

    /**
     * @param swoole_server $server
     * @param $worker_id
     * @param $task_id
     * @param $msg
     */
    public function report(swoole_server $server, $worker_id, $task_id, $msg)
    {
        foreach ($this->monitors as $monitor) {
            $client = new Client($monitor['sock']);
            if ($client->connect($monitor['host'], $monitor['port'], 2)) {
                $client->send(Binary::encode([
                    'worker_id' => $worker_id,
                    'task_id' => $task_id,
                    'msg' => $msg
                ]));
            }
            unset($client);
        }
    }

    /**
     * @param array $config
     * @return Server
     */
    public static function getInstance(array $config = [])
    {
        if (null === static::$instance) {
            static::$instance = new static($config);
        }

        return static::$instance;
    }

    /**
     * @return swoole_server
     */
    public function getSwooleInstance()
    {
        return $this->swoole;
    }

    /**
     * @param array $config
     * @return void
     */
    public static function run(array $config)
    {
        $server = static::getInstance($config);

        $server->start();
    }

    /**
     * @return void
     */
    public function start()
    {
        $this->bootstrap();

        $this->swoole->set($this->config);

        $this->swoole->start();
    }

    /**
     * @return void
     */
    public function status()
    {
        $this->bootstrap();

        $this->swoole->stats();
    }

    /**
     * @return void
     */
    public function reload()
    {
        $this->bootstrap();

        $this->swoole->reload();
    }

    /**
     * @return void
     */
    public function shutdown()
    {
        $this->bootstrap();

        $this->swoole->shutdown();
    }

    /**
     * @return $this
     */
    public function scanOnHandles()
    {
        $handles = get_class_methods($this);

        foreach ($handles as $value) {
            if ('on' == substr($value, 0, 2)) {
                $this->swoole->on(lcfirst(substr($value, 2)), [$this, $value]);
            }
        }
    }

    /**
     * Base start handle. Storage process id.
     *
     * @param swoole_server $server
     * @return void
     */
    public function onStart(swoole_server $server)
    {
        if (null !== ($file = $this->getPid())) {
            if (!is_dir($dir = dirname($file))) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($file, $server->master_pid . PHP_EOL);
        }

        Process::rename(static::SERVER_NAME . ' master');

        Output::output(sprintf("Server %s://%s:%s", $this->getServerType(), $this->getHost(), $this->getPort()));
        foreach ($this->ports as $port) {
            Output::output(sprintf("➜ Listen %s://%s:%s", $this->getServerType($port->type), $port->host, $port->port));
        }
        Output::output(sprintf('Server Master[#%s] is started', $server->master_pid));
    }

    /**
     * Shutdown server process.
     *
     * @param swoole_server $server
     * @return void
     */
    public function onShutdown(swoole_server $server)
    {
        if (null !== ($file = $this->getPid()) && !empty(trim(file_get_contents($file)))) {
            unlink($file);
        }

        Output::output(sprintf('Server Master[#%s] is shutdown ', $server->master_pid));
    }

    /**
     * @param swoole_server $server
     *
     * @return void
     */
    public function onManagerStart(swoole_server $server)
    {
        Process::rename(static::SERVER_NAME . ' manager');

        Output::output(sprintf('Server Manager[#%s] is started', $server->manager_pid));
    }

    /**
     * @param swoole_server $server
     *
     * @return void
     */
    public function onManagerStop(swoole_server $server)
    {
        Output::output(sprintf('Server Manager[#%s] is shutdown.', $server->manager_pid));
    }

    /**
     * @param swoole_server $server
     * @param int $worker_id
     * @return void
     */
    public function onWorkerStart(swoole_server $server, int $worker_id)
    {
        Process::rename(static::SERVER_NAME . ' worker');

        Output::output(sprintf('Server Worker[#%s] is started [#%s]', $server->worker_pid, $worker_id));
    }

    /**
     * @param swoole_server $server
     * @param int $worker_id
     * @return void
     */
    public function onWorkerStop(swoole_server $server, int $worker_id)
    {
        Output::output(sprintf('Server Worker[#%s] is shutdown', $worker_id));
    }

    /**
     * @param swoole_server $server
     * @param int $worker_id
     * @param int $worker_pid
     * @param int $exit_code
     * @return void
     */
    public function onWorkerError(swoole_server $server, int $worker_id, int $worker_pid, int $exit_code)
    {
        Output::output(sprintf('Server Worker[#%s] error. Exit code: [%s]', $worker_pid, $exit_code));
    }
}