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


class ProcessServer
{
    protected $config = [];
    protected $redirectStdout = null;
    protected static $workers = [];

    function __construct(array $config, $redirectStdout = false)
    {
        $this->config = $config;
        $this->redirectStdout = $redirectStdout;
        $this->init();
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
    }

    protected function init()
    {
        $asDaemon = isset($this->config['daemonize']) ? $this->config['daemonize'] : 0;
        if ($asDaemon) {
            \swoole_process::daemon();
        }

        \swoole_process::signal(SIGTERM, function () {
            exit(0);
        });

        \swoole_process::signal(SIGINT, function () {
            exit(0);
        });

        \swoole_process::signal(SIGCHLD, function () {
            if ($ret = \swoole_process::wait(false)) {
                $pid = $ret['pid'];
                if (isset(self::$workers[$pid])) {
                    self::$workers[$pid]->close();
                    \swoole_process::kill($pid, SIGTERM);
                }
            }
        });

    }
}