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

namespace Trensy\Foundation\Command\Httpd;

use Trensy\Config\Config;
use Trensy\Foundation\Application;
use Trensy\Foundation\Shortcut;
use Trensy\Mvc\View\Engine\Bladex\Compilers\BladexCompiler;
use Trensy\Server\WebSocket\WSServer;
use Trensy\Support\Arr;
use Trensy\Support\Dir;
use Trensy\Support\ElapsedTime;
use Trensy\Support\Log;
use Trensy\Support\Tool;

class HttpdBase
{
    use Shortcut;
    public static function operate($cmd, $output, $input)
    {
        $root = Dir::formatPath(ROOT_PATH);

        $config = Config::get("server.httpd");
        $appName = Config::get("server.name");

        if (!$appName) {
            Log::sysinfo("server.name not config");
            exit(0);
        }

        if (!$config) {
            Log::sysinfo("httpd config not config");
            exit(0);
        }

        if (!isset($config['server'])) {
            Log::sysinfo("httpd.server config not config");
            exit(0);
        }

        if ($input->hasOption("daemonize")) {
            $daemonize = $input->getOption('daemonize');
            $config['server']['daemonize'] = $daemonize == 0 ? 0 : 1;
        }

        if (!isset($config['server']['host'])) {
            Log::sysinfo("httpd.server.host config not config");
            exit(0);
        }

        if (!isset($config['server']['port'])) {
            Log::sysinfo("httpd.server.port config not config");
            exit(0);
        }

        $adapter = new Application($root);
        self::doOperate($cmd, $config, $adapter, $root, $appName);
    }


    public static function doOperate($command, array $config, $adapter, $root, $appName)
    {
        $defaultConfig = [
            'daemonize' => 0,
            //worker数量，推荐设置和cpu核数相等
            'worker_num' => 2,
            "dispatch_mode" => 2,
            //reactor数量，推荐2
            'reactor_num' => 2,
            'static_path' => '/tmp/trensy/resource/static',
            "gzip" => 4,
            "static_expire_time" => 86400,
            "task_worker_num" => 5,
            "task_fail_log" => "/tmp/trensy/task_fail_log",
            "task_retry_count" => 2,
            "serialization" => 1,
            "mem_reboot_rate" => 0.8,
            //以下配置直接复制，无需改动
            'open_length_check' => 1,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4,
            'package_max_length' => 8 * 1024 * 1024,//默认8M
            "pid_file" => "/tmp/trensy/pid",
            'open_tcp_nodelay' => 1,
        ];

        $config['server'] = Arr::merge($defaultConfig, $config['server']);

        if (isset($config['server']['log_file']) && !is_dir(dirname($config['server']['log_file']))) {
            mkdir(dirname($config['server']['log_file']), "0777", true);
        }

        if (isset($config['server']['static_path']) && $config['server']['static_path']) {
            $staticPathArr = $config['server']['static_path'];
            if(!is_array($staticPathArr)){
                if(!is_dir($staticPathArr)){
                    mkdir($staticPathArr, "0777", true);
                }
            }else{
                   foreach ($staticPathArr as $v){
                       if(!is_dir($v)){
                           mkdir($v, "0777", true);
                       }
                   }
            }
        }

        $viewCachePath = Config::get("server.httpd.server.view.compile_path");
        if (!is_dir($viewCachePath)) {
            mkdir($viewCachePath, "0777", true);
        }


        $serverName = $appName . "-httpd";
        $serverMaster = $appName . "-httpd-master";
        exec("ps axu|grep " . $serverMaster . "|grep -v grep|awk '{print $2}'", $masterPidArr);
        $masterPid = $masterPidArr ? current($masterPidArr) : null;

        if ($command === 'start' && $masterPid) {
            Log::sysinfo("httpd server already running");
            return;
        }

        if ($command !== 'start' && $command !== 'restart' && !$masterPid) {
            Log::sysinfo("$serverName not run");
            return;
        }
        switch ($command) {
            case 'status':
                if ($masterPid) {
                    Log::sysinfo("$serverName  already running");
                } else {
                    Log::sysinfo("$serverName  not run");
                }
                break;
            case 'start':
                self::start($config, $adapter, $appName);
                break;
            case 'stop':
                self::stop($appName);
                Log::sysinfo("$serverName stop success ");
                break;
            case 'restart':
                $result = self::stop($appName);
                if($result){
                    self::start($config, $adapter, $appName);
                }
                break;
            case 'reload':
                self::reload($appName);
                Log::sysinfo("$serverName reload success ");
                break;
            default :
                return "";
        }
    }
    

    protected static function reload($appName)
    {
        $killStr = $appName . "-httpd-manage";
        exec("ps axu|grep " . $killStr . "|grep -v grep|awk '{print $2}'|xargs kill -USR1", $out, $result);
        return $result;
    }


    protected static function stop($appName)
    {
        $killStr = $appName . "-httpd";
        exec("ps axu|grep " . $killStr . "|grep -v grep|awk '{print $2}'|xargs kill -9", $out, $result);
        self::waitRunCmd("ps axu|grep " . $killStr . "|grep -v grep|awk '{print $2}'");
        return true;
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


    protected static function start($config, $adapter, $appName)
    {
        $swooleServer = new \swoole_websocket_server($config['server']['host'], $config['server']['port']);
        $obj = new WSServer($swooleServer, $config['server'], $adapter, $appName);
        $obj->start();
    }


    protected static function deldir($dir)
    {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    @unlink($fullpath);
                } else {
                    self::deldir($fullpath);
                }
            }
        }

        closedir($dh);
        //删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    protected static function checkCmd($cmd)
    {
        $cmdStr = "command -v " . $cmd;
        exec($cmdStr, $check);
        if (!$check) {
            Log::error("command {$cmd} Not Found");
            return "";
        } else {
            return current($check);
        }
    }


}