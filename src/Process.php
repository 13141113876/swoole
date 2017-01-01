<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Swoole;


use swoole_process;

/**
 * Process manager
 *
 * Class Process
 * @package FastD\Swoole
 */
class Process
{
    /**
     * @var swoole_process
     */
    protected $process;

    /**
     * @var swoole_process[]
     */
    protected $processes = [];

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var bool
     */
    protected $stdout = false;

    /**
     * @var bool
     */
    protected $pipe = true;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $daemonize = false;

    /**
     * Process constructor.
     * @param callable $callback
     * @param bool $stdout
     * @param bool $pipe
     */
    public function __construct(callable $callback, $stdout = false, $pipe = true)
    {
        $this->stdout = $stdout;

        $this->pipe = $pipe;

        $this->callback = $callback;

        $this->process = new swoole_process($callback, $stdout, $pipe);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function daemonize()
    {
        $this->daemonize = true;

        return $this;
    }

    /**
     * @param int $size
     * @return mixed
     */
    public function read($size = 8192)
    {
        return $this->process->read($size);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function write($data)
    {
        return $this->process->write($data);
    }

    /**
     * @param $signo
     * @param callable $callback
     * @return mixed
     */
    public function signal($signo, callable $callback)
    {
        return process_signal($signo, $callback);
    }

    /**
     * @return void
     */
    public function wait(callable $callback, $blocking = true)
    {
        while ($ret = process_wait($blocking)) {
            $callback($ret);
        }
    }

    /**
     * @param $pid
     * @param int $signo
     * @return int
     */
    public function kill($pid, $signo = SIGTERM)
    {
        return process_kill($pid, $signo);
    }

    /**
     * @param $pid
     * @return int
     */
    public function exists($pid)
    {
        return process_kill($pid, 0);
    }

    /**
     * @return mixed
     */
    public function start()
    {
        if (!empty($this->name)) {
            $this->process->name($this->name);
        }
        if (true === $this->daemonize) {
            $this->process->daemon();
        }

        return $this->process->start();
    }

    /**
     * @param int $length
     * @return int
     */
    public function fork($length = 1)
    {
        // run parent process
        $this->start();
        // new sub process
        for ($i = 0; $i < $length; $i++) {
            $process = new static($this->callback, $this->stdout, $this->pipe);
            if (!empty($this->name)) {
                $process->name($this->name);
            }
            if (true === $this->daemonize) {
                $process->daemon();
            }
            $pid = $process->start();
            if (false === $pid) {
                return -1;
            }
            $this->processes[$pid] = $process;
        }
        return 0;
    }

    /**
     * @return swoole_process[]
     */
    public function getChildProcesses()
    {
        return $this->processes;
    }

    /**
     * @return swoole_process
     */
    public function getProcess()
    {
        return $this->process;
    }
}