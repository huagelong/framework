<?php
/**
 *  exception 格式化
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

namespace Trensy\Support;


class Exception
{

    /**
     *  格式化 eception
     *
     * @param $e
     * @return string
     */
    public static function formatException($e)
    {
        $message = "Exception Error : [".$e->getCode()."] " . $e->getMessage();
        $message .= " in " . $e->getFile() . ":" . $e->getLine() . "\n";
        $message .= "Stack trace\n";

        $trace = explode("\n", $e->getTraceAsString());
//        $trace = array_slice($trace,0,7);
        $message .= implode("\n", $trace);
        return $message;
    }

    public static function debugException($message = "")
    {
        $log = "$message\nStack trace:\n";
        $trace = debug_backtrace(1);
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
        return $log;
    }

}