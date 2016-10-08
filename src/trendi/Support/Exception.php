<?php
/**
 *  exception 格式化
 *
 * User: Peter Wang
 * Date: 16/9/15
 * Time: 下午9:18
 */

namespace Trendi\Support;


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
        $message = "Exception Error : " . $e->getMessage();
        $message .= " in " . $e->getFile() . ":" . $e->getLine() . "\n";
        $message .= "Stack trace\n";
        $message .= $e->getTraceAsString();
        return $message;
    }

}