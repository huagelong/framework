<?php
/**
 * User: Peter Wang
 * Date: 16/9/15
 * Time: 下午10:19
 */

namespace Trendi\Foundation\Command\Server;

use Trendi\Config\Config;
use Trendi\Support\Dir;


class ServerBase
{
    public static function operate($cmd, $output, $input)
    {
        $root = Dir::formatPath(ROOT_PATH);
        Config::setConfigPath($root . "config");

        $daemonizeStr = "";
        if (($cmd == 'start') && $input->hasOption("daemonize")) {
            $daemonize = $input->getOption('daemonize');
            $daemonizeStr = $daemonize == 0 ? "" : "-d";
        }

        $config = Config::get("server");
        if (!$config) {
            $output->writeln("<info>server config not config</info>");
            return;
        }
        self::doOperate($cmd, $daemonizeStr, $config);
        exit(0);
    }


    public static function doOperate($command, $daemonizeStr, array $config)
    {
        self::runCmd($command, $config, $daemonizeStr);
        if ($daemonizeStr) {
            \swoole_process::wait(false);
        } else {
            \swoole_process::wait();
        }

    }

    protected static function runCmd($type, $config, $daemonizeStr)
    {
        $runFileName = $_SERVER['SCRIPT_FILENAME'];
        $phpbin = self::getPhpBinary();
        $servers = $config['servers'];
        if ($servers) {
            foreach ($servers as $v) {
                $params = [$runFileName, $v . ":" . $type];
                if ($daemonizeStr) array_push($params, $daemonizeStr);
                self::process($phpbin, $params);
                self::check($config);
            }
        }
    }

    protected static function check($config)
    {
        $name = $config['name'];
        $count = 0;
        $time = time();
        while (1){
            exec("ps axu|grep ".$name."|awk '{print $2}'", $masterArr);
            if((time()-$time)>30){
                break;
            }
            if($count ==0){
                $count = count($masterArr);
                continue;
            }elseif(count($masterArr)==$count)
            {
                continue;
            }
            break;
        }
    }

    protected static function process($phpbin, $param)
    {
        $process = new \swoole_process(function (\swoole_process $worker) use ($phpbin, $param) {
            $worker->exec($phpbin, $param);
        }, false);
        $process->start();
    }

    protected static function getPhpBinary()
    {
        $executableFinder = new \Symfony\Component\Process\PhpExecutableFinder();

        return $executableFinder->find();
    }
}