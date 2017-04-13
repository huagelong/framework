<?php
/**
 * process server
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

namespace Trensy\Server;


use Trensy\Foundation\Shortcut;

abstract class ProcessServer
{
    use Shortcut;
    protected $config = [];
    protected $redirectStdout = null;
    protected static $workers = [];

    public function __construct($daemonize, $redirectStdout = false)
    {
        $this->redirectStdout = $redirectStdout;
        $this->init($daemonize);
    }


    abstract function sigchld();


    public static function getWorkers()
    {
        return self::$workers;
    }


    public function unsetWorker($pid)
    {
        if (isset(self::$workers[$pid])) {
            self::$workers[$pid]->close();
            \swoole_process::kill($pid, SIGTERM);
            unset(self::$workers[$pid]);
        }
    }

    /**
     * 添加子进程 
     *
     * @param $callBack
     */
    public function add($callBack)
    {
        $process = new \swoole_process($callBack, $this->redirectStdout);
        $pid = $process->start();
        self::$workers[$pid] = $process;
        return $pid;
    }

    protected function init($asDaemon)
    {
        if ($asDaemon) {
            \swoole_process::daemon();
        }

        \swoole_process::signal(SIGTERM, function () {var_dump(1);
            exit(0);
        });

        \swoole_process::signal(SIGINT, function () {var_dump(2);
            exit(0);
        });

        $this->sigchld();

    }
}