<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Foundation\Command\Server;

use Trensy\Config\Config;
use Trensy\Foundation\Shortcut;
use Trensy\Support\Exception;
use Trensy\Support\Log;
use Trensy\Support\PhpExecutableFinder;


class ServerBase
{
    use Shortcut;

    protected static $cmdHelp = null;

    public static function operate($cmd, $cmdObj, $input)
    {
        $options = [];
        if (($cmd == 'start' || $cmd == 'restart') && $input->hasOption("daemonize")) {
            $daemonize = $input->getOption('daemonize');
            if ($daemonize) $options["daemonize"] = "-d";
        }

        if ($input->hasOption("option")) {
            $option = $input->getOption('option');
            if ($option) $options["option"] = $option;
        }

        $config = Config::get("server");
        if (!$config) {
            Log::sysinfo("server config not config");
            return;
        }
        $str = 'Welcome To Trensy!';
        Log::show($str);

        $tmpPath = "tmp path is : ".STORAGE_PATH;
        Log::show($tmpPath);

        try{
            self::doOperate($cmd, $options, $config, $cmdObj);
        }catch (\Exception $e){
            Log::error(Exception::formatException($e));
        }
        sleep(1);
        exit(0);
    }
    
    
    public static function doOperate($command, $options, array $config, $cmdObj)
    {
        self::runCmd($command, $config, $options, $cmdObj);
        $daemonizeStr = self::array_isset($options, "daemonize");
        if ($daemonizeStr) {
            \swoole_process::wait(false);
        } else {
            \swoole_process::wait();
        }

    }

    protected static function waitRunCmd($cmd)
    {
        exec($cmd, $out, $result);
        if($out){
            sleep(1);
            self::waitRunCmd($cmd);
        }
        return true;
    }

    protected static function runCmd($type, $config, $options, $cmdObj)
    {

        if(strtolower(PHP_OS) == 'darwin'){
            //mac 直接根据端口结束进程
            if(in_array($type, ["stop", "restart"])){
                exec("ps aux|grep trensy|grep -v server:restart|grep -v server:stop|grep -v grep|grep -v PID|awk '{print $2}'|xargs kill -9", $out, $result);
                self::waitRunCmd("ps aux|grep trensy|grep -v server:restart|grep -v server:stop|grep -v grep|grep -v PID|awk '{print $2}'");
            }
        }

        $daemonizeStr = self::array_isset($options, "daemonize");
        $option = self::array_isset($options, "option");
        $runFileName = $_SERVER['SCRIPT_FILENAME'];
        $phpbin = self::getPhpBinary();
        $servers = $config['servers'];

        if ($servers) {
            $name = $config['name'];
            foreach ($servers as $v) {
                if(!isset($config[$v]) || !$config[$v]) continue;
                $cmdName = $v . ":" . $type;
                $cmdDefined = $cmdObj->getApplication()->has($cmdName);
                if(!$cmdDefined){
                    $cmdName = $v;
                }
                $params = [$runFileName, $cmdName];
                if ($daemonizeStr) array_push($params, $daemonizeStr);
                if ($option) {
                    $optionTmp = explode(",", $option);
                    foreach ($optionTmp as $v) {
                        array_push($params, $v);
                    }
                }
                self::process($phpbin, $params, $cmdObj);
                self::check($name);
            }
        }
    }

    protected static function check($name)
    {
        $count = -1;
        $time = time();
        while (1) {
            sleep(1);
            exec("ps axu|grep " . $name . "|grep -v grep|awk '{print $2}'", $masterArr);
            if ((time() - $time) > 10) {
                break;
            }
            if ($count == -1) {
                $count = count($masterArr);
                continue;
            } elseif (count($masterArr) == $count) {
                continue;
            }
            break;
        }
    }

    protected static function process($phpbin, $param, $cmdObj)
    {
        $process = new \swoole_process(function (\swoole_process $worker) use ($phpbin, $param, $cmdObj) {
            $param = self::getCmdHelp($param, $cmdObj);
            $worker->exec($phpbin, $param);
        }, false);
        $process->start();
    }

    protected static function getCmdHelp($param, $cmdObj)
    {
        $paramTmp = $param;
        $cmdName = self::array_isset($param, 1);
        if (!$cmdName) return;
        
        $obj = $cmdObj->getCmdDefinition($cmdName);
        $op = array_slice($param, 2);
        $tmp = [];
        foreach ($op as $v) {
            $shortName = substr(ltrim($v, "-"), 0, 1);
//            dump($shortName);
            $check = $obj->hasShortcut($shortName);
            if ($check) {
                $tmp[] = $v;
                continue;
            }

            $shortName = current(explode("=", ltrim($v, "-")));
//            dump($shortName);
            $check = $obj->hasOption($shortName);
            if ($check) {
                $tmp[] = $v;
            }
        }
        $newParam = array_slice($paramTmp, 0, 2);

        if ($tmp) $newParam = array_merge($newParam, $tmp);
//        dump($newParam);
        return $newParam;
    }

    protected static function getPhpBinary()
    {
        $executableFinder = new PhpExecutableFinder();

        return $executableFinder->find();
    }
}