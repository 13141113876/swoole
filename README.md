# FastD Swoole

[![Latest Stable Version](https://poser.pugx.org/fastd/swoole/v/stable)](https://packagist.org/packages/fastd/swoole) [![Total Downloads](https://poser.pugx.org/fastd/swoole/downloads)](https://packagist.org/packages/fastd/swoole) [![Latest Unstable Version](https://poser.pugx.org/fastd/swoole/v/unstable)](https://packagist.org/packages/fastd/swoole) [![License](https://poser.pugx.org/fastd/swoole/license)](https://packagist.org/packages/fastd/swoole)

高性能网络服务组件. 提供底层服务封装, 基础管理及客户端调用功能. 使用 `composer` 进行管理, 可在此基础上进行封装整合.

## ＃环境要求

* PHP 7+

* Swoole 1.8+ (期待2.0)

源码地址: [swoole](https://github.com/swoole/swoole-src)

pecl 安装

```shell
pecl install swoole
```

### ＃可选扩展

PHP >= 7.0 的安装 2.0 版本.

源码地址: [inotify](http://pecl.php.net/package/inotify)

pecl 安装

```shell
pecl install inotify
```

### ＃安装

```
{
    "fastd/swoole": "2.0.x-dev"
}
```

## ＃使用

服务继承 `FastD\Swoole\Server\Server`, 实现 `doWork` 方法, 服务器在接收信息 `onReceive` 回调中会调用 `doWork` 方法。

具体逻辑在 `doWork` 方法中实现。

服务器通过 `run` 方法执行, `run` 方法中注入配置, 配置按照 `swoole` 原生扩展参数配置。

配置扩展了几个常用参数.

```php
return [
    'pid' => 'pid 文件目录地址',
    'host' => '机器ip',
    'port' => '机器端口',
    'mode' => '服务模式,参考官网',
    'sock' => 'sock类型,参考官网',
];
```

```php
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
        $server->send($fd, $data, $from_id);
        $server->close($fd);
    }
}

DemoServer::run([]);
```

同理, `Http` 服务器扩展 `Server` 类, 实现 `doRequest` 方法,实现具体逻辑。

```php
use FastD\Swoole\Server\HttpServer;

class Http extends HttpServer
{
    /**
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     * @return mixed
     */
    public function doRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $response->end('hello world');
    }
}

Http::run([]);
```

服务 `Service` 管理, 修改服务 `Service` 管理, 可以通过注入服务, 对其进行 `{start|status|stop|reload}` 等操作管理。

```php
use FastD\Swoole\Server\Server;
use FastD\Swoole\Console\Service;

class Demo extends Server
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
        // TODO: Implement doWork() method logic.
    }
}

Service::server(Demo::class, [

])->start();
```

`Service` 通过 `server($server, array $config)` 注入服务, 实现管理。

# License MIT
