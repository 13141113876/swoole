<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/7/12
 * Time: 下午4:17
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Swoole\Server;

use FastD\Swoole\SwooleInterface;

/**
 * Interface ServerInterface
 *
 * @package FastD\Swoole\Server
 */
interface ServerInterface extends SwooleInterface
{
    /**
     * @return int
     */
    public function getPid();

    /**
     * @return mixed
     */
    public function start();

    /**
     * @return string
     */
    public function getUser();

    /**
     * @param $user
     * @return $this
     */
    public function setUser($user);

    /**
     * @return string
     */
    public function getGroup();

    /**
     * @param $group
     * @return $this
     */
    public function setGroup($group);

    /**
     * @param      $name
     * @param null $value
     * @return $this
     */
    public function setConfig($name, $value = null);
}