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

}