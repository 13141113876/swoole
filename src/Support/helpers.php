<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

use FastD\Swoole\Exceptions\AddressIllegalException;
use FastD\Swoole\Exceptions\CantSupportSchemeException;
use FastD\Swoole\Server;
use FastD\Swoole\Support\Output;

/**
 * @param Server $server
 * @return Server $server
 */
function handle (Server $server) {
    $handles = get_class_methods($server);
    $isListenerPort = false;
    if ('swoole_server_port' == get_class($server->getSwoole())) {
        $isListenerPort = true;
    }
    foreach ($handles as $value) {
        if ('on' == substr($value, 0, 2)) {
            if ($isListenerPort) {
                if (in_array($value, ['onConnect', 'onClose', 'onReceive', 'onPacket', 'onReceive'])) {
                    echo $value . PHP_EOL;
                    $server->getSwoole()->on(lcfirst(substr($value, 2)), [$server, $value]);
                }
            } else {
                $server->getSwoole()->on(lcfirst(substr($value, 2)), [$server, $value]);
            }
        }
    }
    return $server;
}

/**
 * @param $name
 */
function process_rename ($name) {
    // hidden Mac OS error。
    set_error_handler(function () {});

    if (function_exists('cli_set_process_title')) {
        cli_set_process_title($name);
    } else if (function_exists('swoole_set_process_name')) {
        swoole_set_process_name($name);
    }

    restore_error_handler();
}

/**
 * @param $swoole
 * @param $sock
 * @return string
 */
function server_type ($swoole, $sock = null) {
    switch (get_class($swoole)) {
        case 'swoole_http_server':
            return 'http';
        case 'swoole_websocket_server':
            return 'ws';
        case 'swoole_server':
        case 'swoole_server_port':
            return ($sock === SWOOLE_SOCK_UDP || $sock === SWOOLE_SOCK_UDP6) ? 'udp' : 'tcp';
        default:
            return 'unknown';
    }
}

/**
 * @param $address
 * @return mixed
 */
function parse_address ($address) {
    if (false === ($info = parse_url($address))) {
        throw new AddressIllegalException($address);
    }

    switch (strtolower($info['scheme'])) {
        case 'tcp':
        case 'unix':
            $sock = SWOOLE_SOCK_TCP;
            break;
        case 'udp':
            $sock = SWOOLE_SOCK_UDP;
            break;
        case 'http':
        case 'ws':
            $sock = null;
            break;
        default:
            throw new CantSupportSchemeException($info['scheme']);
    }

    $info['sock'] = $sock;

    return $info;
}

/**
 * @param $message
 * @return void
 */
function output ($message) {
    $message = sprintf("[%s]\t", date('Y-m-d H:i:s')) . $message;
    Output::output($message);
}

/**
 * @param array $keys
 * @param array $columns
 */
function output_table (array $keys, array $columns) {
    Output::table($keys, $columns);
}

/**
 * Kill somebody
 *
 * @param $pid
 * @param int $signo
 * @return int
 */
function process_kill ($pid, $signo = SIGTERM) {
    return swoole_process::kill($pid, $signo);
}

/**
 * @param bool $blocking
 * @return array
 */
function process_wait ($blocking = true) {
    return swoole_process::wait($blocking);
}

/**
 * @param bool $nochdir
 * @param bool $noclose
 * @return mixed
 */
function process_daemon ($nochdir = true, $noclose = true) {
    return swoole_process::daemon($nochdir, $noclose);
}

/**
 * @param $signo
 * @param callable $callback
 * @return mixed
 */
function process_signal ($signo, callable $callback) {
    return swoole_process::signal($signo, $callback);
}

/**
 * @param $interval
 * @param $type
 * @return bool
 */
function process_alarm ($interval, $type = ITIMER_REAL) {
    return swoole_process::alarm($interval, $type);
}

/**
 * @param array $cpus
 * @return mixed
 */
function process_affinity (array $cpus) {
    return swoole_process::setaffinity($cpus);
}

/**
 * @param $interval
 * @param callable $callback
 * @return mixed
 */
function timer_tick ($interval, callable $callback) {
    return swoole_timer_tick($interval, $callback);
}

/**
 * @param $timerId
 * @return mixed
 */
function timer_clear ($timerId) {
    return swoole_timer_clear($timerId);
}
