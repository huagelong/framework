<?php
/**
 * error exception handle
 *
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Foundation\Bootstrap;

use Trensy\Config\Config;
use Trensy\Support\Exception;
use Trensy\Support\Log;

class ErrorHandleBootstrap
{

    protected static $instance = null;

    public static function getInstance($errorReportingLevel = E_ALL, $displayErrors = true)
    {
        if (self::$instance) return self::$instance;

        return self::$instance = new self($errorReportingLevel, $displayErrors);
    }

    public function __construct()
    {
        ini_set("swoole.display_errors", false);
        ini_set('display_errors', false);

        error_reporting(E_ALL ^ E_NOTICE);

        set_exception_handler([$this, 'handleException']);

        set_error_handler([$this, 'handleError']);

        register_shutdown_function([$this, 'handleShutdown']);

    }

    /**
     *  error handle
     *
     * @param $level
     * @param $message
     * @param string $file
     * @param int $line
     * @param array $context
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        restore_error_handler();
        $message = "WARNING  with message '{$message}' in " . $file . ':' . $line . "\n";
        $compile = Config::get("server.httpd.server.view.compile_path");
        $compile = realpath($compile);
        if(stristr($file, $compile)){
            echo($message);
        }else{
            Log::warn($message);
        }
    }

    /**
     * exception handle
     *
     * @param $e
     */
    public function handleException($e)
    {
        restore_exception_handler();
        Log::error(Exception::formatException($e));
    }

    /**
     * register_shutdown_function
     */
    public function handleShutdown()
    {
        $error = error_get_last();
        if (isset($error['type'])) {
            switch ($error['type']) {
                case E_ERROR :
                case E_PARSE :
                case E_CORE_ERROR :
                case E_COMPILE_ERROR :
                    $message = $error['message'];
                    $file = $error['file'];
                    $line = $error['line'];
                    $log = "$message ($file:$line)\nStack trace:\n";
                    $trace = debug_backtrace();
                    foreach ($trace as $i => $t) {
                        if (!isset($t['file'])) {
                            $t['file'] = 'unknown';
                        }
                        if (!isset($t['line'])) {
                            $t['line'] = 0;
                        }
                        if (!isset($t['function'])) {
                            $t['function'] = 'unknown';
                        }
                        $log .= "#$i {$t['file']}({$t['line']}): ";
                        if (isset($t['object']) and is_object($t['object'])) {
                            $log .= get_class($t['object']) . '->';
                        }
                        $log .= "{$t['function']}()\n";
                    }
                Log::error($log);
                default:
                    break;
            }
        }
    }

    public function __destruct()
    {
    }
}