<?php
/**
 * User: Peter Wang
 * Date: 16/9/15
 * Time: 下午2:41
 */

namespace Trendi\Foundation\Bootstrap;

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

    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        restore_error_handler();
        $message = "WARNING  with message '{$message}' in " . $file . ':' . $line . "\n";
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
//        $trace = array_shift($trace);
        if ($trace) {
            foreach ($trace as $v) {
                $class = isset($v['class']) ? $v['class'] : "";
                $type = isset($v['type']) ? $v['type'] : "";
                $function = isset($v['function']) ? $v['function'] : "";
                $message .= $v['file'] . "(" . $v['line'] . "): " . $class . $type . $function . "\n";
            }
        }
        dump($message);
    }

    public function handleException($e)
    {
        restore_exception_handler();
        dump(\Trendi\Support\Exception::formatException($e));
    }

    public function handleShutdown()
    {
//        $error = error_get_last();
//        $isFatalError = isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING]);
//        if ($isFatalError) {
//            throw new ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
//        }
    }

    public function __destruct()
    {
    }
}