<?php
/**
 * User: Peter Wang
 * Date: 16/9/15
 * Time: 下午10:19
 */

namespace Trendi\Foundation\Command\Pool;

use Trendi\Config\Config;
use Trendi\Pool\PoolServer;
use Trendi\Support\Arr;
use Trendi\Support\Dir;
use Trendi\Support\Serialization\Serialization;

class PoolBase
{
    public static function operate($cmd, $output, $input)
    {

        $root = Dir::formatPath(ROOT_PATH);
        Config::setConfigPath($root . "config");
        $config = Config::get("server.pool");
        $appName = Config::get("server.name");

        if (!$appName) {
            $output->writeln("<info>server.name not config</info>");
            exit(0);
        }

        if (!$config) {
            $output->writeln("<info>pool config not config</info>");
            exit(0);
        }

        if (!isset($config['server'])) {
            $output->writeln("<info>pool.server config not config</info>");
            exit(0);
        }

        if (!isset($config['server']['pool_worker_number'])) {
            $output->writeln("<info>pool.server.pool_worker_number config not config</info>");
            exit(0);
        }

        if ($input->hasOption("daemonize")) {
            $daemonize = $input->getOption('daemonize');
            $config['server']['daemonize'] = $daemonize == 0 ? 0 : 1;
        }

        if (!isset($config['server']['host'])) {
            $output->writeln("<info>pool.server.host config not config</info>");
            exit(0);
        }

        if (!isset($config['server']['port'])) {
            $output->writeln("<info>pool.server.port config not config</info>");
            exit(0);
        }
        self::doOperate($cmd, $config, $root, $appName, $output);
    }


    public static function doOperate($command, array $config, $root, $appName, $output)
    {
        $defaultConfig = [
            'daemonize' => 0,
            //worker数量，推荐设置和cpu核数相等
            'worker_num' => 2,
            //reactor数量，推荐2
            'reactor_num' => 2,
            "mem_reboot_rate" => 0.8,
            "dispatch_mode" => 2,
            'static_path' => $root . '/public',
            "gzip" => 4,
            "static_expire_time" => 86400,
            "task_worker_num" => 5,
            "task_fail_log" => "/tmp/task_fail_log",
            "task_retry_count" => 2,
            "serialization" => 1,
            //以下配置直接复制，无需改动
            'open_length_check' => 1,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4,
            'package_max_length' => 2000000,
            "pid_file" => "/tmp/pid",
            'heartbeat_check_interval' => 5,
            'heartbeat_idle_time' => 10,
        ];

        $config['server'] = Arr::merge($defaultConfig, $config['server']);

        $serverName = $appName . "-pool-server";
        exec("ps axu|grep " . $serverName . "$|awk '{print $2}'", $masterPidArr);
        $masterPid = $masterPidArr ? current($masterPidArr) : null;

        if ($command === 'start' && $masterPid) {
            $output->writeln("<info>[$serverName] already running</info>");
            return;
        }

        if ($command !== 'start' && $command !== 'restart' && !$masterPid) {
            $output->writeln("<info>[$serverName] not run</info>");
            return;
        }
        // execute command.
        switch ($command) {
            case 'status':
                if ($masterPid) {
                    $output->writeln("<info>[$serverName] already running</info>");
                } else {
                    $output->writeln("<info>[$serverName] not run</info>");
                }
                break;
            case 'start':
                $swooleServer = new \swoole_server($config['server']['host'], $config['server']['port']);

                $serialization = Serialization::get($config['server']['serialization']);
                $serialization->setBodyOffset($config['server']['package_body_offset']);
                $obj = new PoolServer($swooleServer, $serialization, $config, $root, $appName);
                $obj->start();
                break;
            case 'stop':
                $output->writeln("<info>[$serverName] is stoping ...</info>");
                // Send stop signal to master process.
                $masterPid && posix_kill($masterPid, SIGTERM);
                // Timeout.
                $timeout = 5;
                $start_time = time();
                // Check master process is still alive?
                while (1) {
                    $masterIsAlive = $masterPid && posix_kill($masterPid, 0);
                    if ($masterIsAlive) {
                        // Timeout?
                        if ((time() - $start_time) >= $timeout) {
                            $output->writeln("<error>[$serverName] stop fail </error>");
                            return;
                        }
                        // Waiting amoment.
                        usleep(10000);
                        continue;
                    }
                    // Stop success.
                    $output->writeln("<info>[$serverName] stop success </info>");
                    return;
                    break;
                }
                break;
            case 'restart':
                $output->writeln("<info>[$serverName] is restarting ...</info>");
                $masterPid && posix_kill($masterPid, SIGTERM);
                $timeout = 5;
                $start_time = time();
                // Check master process is still alive?
                while (1) {
                    $masterIsAlive = $masterPid && posix_kill($masterPid, 0);
                    if ($masterIsAlive) {
                        // Timeout?
                        if ((time() - $start_time) >= $timeout) {
                            $output->writeln("<error>[$serverName] restart fail </error>");
                            return;
                        }
                        // Waiting amoment.
                        usleep(10000);
                        continue;
                    }
                    $swooleServer = new \swoole_server($config['server']['host'], $config['server']['port']);
                    $serialization = Serialization::get($config['server']['serialization']);
                    $serialization->setBodyOffset($config['server']['package_body_offset']);
                    $obj = new PoolServer($swooleServer, $serialization, $config, $root, $appName);
                    $obj->start();
                    $output->writeln("<info>[$serverName] restart success </info>");
                    break;
                }
                break;
            default :
                return "";
        }
    }

}