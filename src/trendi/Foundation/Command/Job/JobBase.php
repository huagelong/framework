<?php
/**
 * User: Peter Wang
 * Date: 16/9/15
 * Time: 下午10:19
 */

namespace Trendi\Foundation\Command\Job;

use Trendi\Config\Config;
use Trendi\Job\JobServer;
use Trendi\Support\Arr;
use Trendi\Support\Dir;
use Trendi\Support\ElapsedTime;

class JobBase
{
    public static function operate($cmd, $output, $input)
    {
        ElapsedTime::setStartTime(ElapsedTime::SYS_START);
        $root = Dir::formatPath(ROOT_PATH);
        Config::setConfigPath($root . "config");
        $config = Config::get("server.job");
        $appName = Config::get("server.name");

        if (!$appName) {
            $output->writeln("<info>server.name not config</info>");
            exit(0);
        }

        if (!$config) {
            $output->writeln("<info>job config not config</info>");
            exit(0);
        }

        if (!isset($config['server'])) {
            $output->writeln("<info>job.server config not config</info>");
            exit(0);
        }


        if ($input->hasOption("daemonize")) {
            $daemonize = $input->getOption('daemonize');
            $config['server']['daemonize'] = $daemonize == 0 ? 0 : 1;
        }

        self::doOperate($cmd, $config, $root, $appName, $output);
    }


    public static function doOperate($command, array $config, $root, $appName, $output)
    {
        $defaultConfig = [
            //是否后台运行, 推荐设置0
            'daemonize' => 0,
            //worker数量，推荐设置和cpu核数相等
            'worker_num' => 2,
            "mem_reboot_rate" => 0.8,//可用内存达到多少自动重启
            "serialization" => 1
        ];

        $config['server'] = Arr::merge($defaultConfig, $config['server']);
        $config['server']['name'] = $appName;

        $serverName = $appName . "-job-master";
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
            case 'clear':
                $jobServer = new JobServer($config, $root);
                $jobServer->clear();
                break;
            case 'start':
                self::start($config, $root);
                break;
            case 'stop':
                self::stop($appName);
                $output->writeln("<info>[$serverName] stop success </info>");
                break;
            case 'restart':
                self::stop($appName);
               self::start($config, $root);
                break;
            default :
                exit(0);
        }
    }

    protected static function stop($appName)
    {
        $killStr = $appName . "-job";
        exec("ps axu|grep " . $killStr . "|awk '{print $2}'|xargs kill -9", $masterPidArr);
    }

    protected static function start($config, $root)
    {
        $jobServer = new JobServer($config, $root);
        $jobServer->start();
    }

}