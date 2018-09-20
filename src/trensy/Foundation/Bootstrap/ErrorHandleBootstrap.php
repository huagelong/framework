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
 * @version         3.0.0
 */

namespace Trensy\Foundation\Bootstrap;

use Trensy\Config;
use Trensy\Support\Exception;
use Trensy\Log;

class ErrorHandleBootstrap
{

    protected static $instance = null;

    public static function getInstance($errorReportingLevel = E_ALL, $displayErrors = true)
    {
        if (self::$instance) return self::$instance;

        return self::$instance = new self($errorReportingLevel, $displayErrors);
    }

    public function __construct($errorReportingLevel, $displayErrors)
    {
        ini_set('display_errors', $displayErrors);

        error_reporting($errorReportingLevel);

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
    public function handleError($errno, $message, $file = '', $line = 0, $context = [])
    {
        restore_error_handler();

        switch ($errno) {
            case E_USER_ERROR:
                throw new \ErrorException($message, $errno, null, $file, $line);
                break;
            case E_WARNING:
            case E_USER_WARNING:
                throw new \ErrorException($message, $errno, null, $file, $line);
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                throw new \ErrorException($message, $errno, null, $file, $line);
                break;
            default:
                $message =  "Unknown error type: [".$errno."] ".$message." in " . $file . ":" . $line . "\n";
                Log::error($message);
                break;
        }

        /* Don't execute PHP internal error handler */
        return true;
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
        }
    }

    public function __destruct()
    {
    }
}