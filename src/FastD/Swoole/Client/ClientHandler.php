<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/7/12
 * Time: 下午4:09
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Swoole\Client;

use FastD\Swoole\SwooleInterface;

class ClientHandler implements ClientHandlerInterface
{
    protected $prepareBind = [
        'connect'   => 'onConnect',
        'error'     => 'onError',
        'receive'   => 'onReceive',
        'close'     => 'onClose'
    ];

    protected $on;

    protected $swoole;

    public function __construct(array $on = null)
    {
        if (null === $on) {
            $on = array_keys($this->prepareBind);
        }

        $this->setPrepareBind($on);
    }

    /**
     * @param array $on
     * @return $this
     */
    public function setPrepareBind(array $on = null)
    {
        foreach ($on as $name) {
            $this->on[$name] = $this->prepareBind[$name];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getPrepareBind()
    {
        return $this->prepareBind;
    }

    /**
     * @param SwooleInterface $swooleInterface
     * @return $this
     */
    public function handle(SwooleInterface $swooleInterface)
    {
        $this->swoole = $swooleInterface;

        foreach ($this->prepareBind as $name => $callback) {
            $swooleInterface->on($name, [$this, $callback]);
        }

        return $this;
    }

    public function onConnect(\swoole_client $client)
    {
        echo 'connect';
    }

    public function onReceive(\swoole_client $client, $data)
    {
        echo 'receive';
    }

    public function onError(\swoole_client $client)
    {
        echo 'error';
    }

    public function onClose(\swoole_client $client)
    {
        echo 'close';
    }
}