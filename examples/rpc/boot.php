<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/7/11
 * Time: 下午5:51
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

include __DIR__ . '/../../vendor/autoload.php';

$server = \FastD\Swoole\Rpc\Rpc::create('http://127.0.0.1:9321');

$server->setUser('vagrant');
$server->setGroup('vagrant');
$server->rename('demo swoole');

$invoker = new \FastD\Swoole\Invoker($server);

return $invoker;