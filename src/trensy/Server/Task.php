<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */


namespace Trensy\Server;

use Trensy\Foundation\Exception\InvalidArgumentException;
use Trensy\Config;
use Trensy\Di;

class Task extends TaskRunAbstract
{

    public function start($data)
    {
        list($task, $params) = $data;
        if (is_string($task)) {
            $taskConfig = Config::get("app.task");
            $taskClass = isset($taskConfig[$task]) ? $taskConfig[$task] : null;
            if (!$taskClass) {
                throw new InvalidArgumentException(" task not config ");
            }

            register_shutdown_function(function() use($taskClass,$params){
                $obj = Di::get($taskClass);

                if (!method_exists($obj, "perform")) {
                    throw new InvalidArgumentException(" task method perform not config ");
                }
                call_user_func_array([$obj, "perform"], $params);
            });

            return true;
        }
    }

    public function __call($name, $arguments)
    {
        $this->start(func_get_args());
    }

    public function finish($data)
    {

    }
}