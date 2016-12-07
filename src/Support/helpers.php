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
use FastD\Swoole\Support\Output;

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

function output_table (array $keys, array $columns) {
    Output::table($keys, $columns);
}