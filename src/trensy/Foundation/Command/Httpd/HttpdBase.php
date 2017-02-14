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
use Trensy\Mvc\View\Engine\Bladex\Compilers\BladexCompiler;
use Trensy\Server\WebSocket\WSServer;
use Trensy\Support\Arr;
use Trensy\Support\Dir;
use Trensy\Support\ElapsedTime;
use Trensy\Support\Log;
use Trensy\Support\Tool;

class HttpdBase
{
    public static function operate($cmd, $output, $input)
    {
        ElapsedTime::setStartTime(ElapsedTime::SYS_START);

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
            'static_path' => $root . '/public',
            "gzip" => 4,
            "static_expire_time" => 86400,
            "task_worker_num" => 5,
            "task_fail_log" => "/tmp/task_fail_log",
            "task_retry_count" => 2,
            "serialization" => 1,
            "mem_reboot_rate" => 0,
            //以下配置直接复制，无需改动
            'open_length_check' => 1,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4,
            'package_max_length' => 8 * 1024 * 1024,//默认8M
            "pid_file" => "/tmp/pid",
            'open_tcp_nodelay' => 1,
        ];

        $config['server'] = Arr::merge($defaultConfig, $config['server']);

        if (isset($config['server']['log_file']) && !is_dir(dirname($config['server']['log_file']))) {
            mkdir(dirname($config['server']['log_file']), "0777", true);
        }

        if (isset($config['server']['static_path']) && !is_dir($config['server']['static_path'])) {
            mkdir($config['server']['static_path'], "0777", true);
        }

        $viewCachePath = Config::get("server.httpd.server.view.compile_path");
        if (!is_dir($viewCachePath)) {
            mkdir($viewCachePath, "0777", true);
        }


        $serverName = $appName . "-httpd-master";
        exec("ps axu|grep " . $serverName . "$|awk '{print $2}'", $masterPidArr);
        $masterPid = $masterPidArr ? current($masterPidArr) : null;

        if ($command === 'start' && $masterPid) {
            Log::sysinfo("httpd server already running");
            return;
        }

        self::BladeCompileInit();

        if ($command !== 'start' && $command !== 'restart' && !$masterPid) {
            Log::sysinfo("[$serverName] not run");
            return;
        }
        switch ($command) {
            case 'status':
                if ($masterPid) {
                    Log::sysinfo("[$serverName]  already running");
                } else {
                    Log::sysinfo("[$serverName]  not run");
                }
                break;
            case 'start':
                self::start($config, $adapter, $appName);
                break;
            case 'stop':
                self::stop($appName);
                Log::sysinfo("[$serverName] stop success ");
                break;
            case 'restart':
                self::stop($appName);
                self::start($config, $adapter, $appName);
                break;
            default :
                return "";
        }
    }

    protected static function BladeCompileInit()
    {
        $viewStaticPath = Config::get("server.httpd.server.static_path");
        $viewStaticPath = Dir::formatPath($viewStaticPath);
        $rootPath = Dir::formatPath(ROOT_PATH);
        $staticPath = "/" . str_replace($rootPath, "", $viewStaticPath);
        BladexCompiler::setStaticPath($staticPath);
    }
    


    protected static function stop($appName)
    {
        $killStr = $appName . "-httpd";
        exec("ps axu|grep " . $killStr . "|awk '{print $2}'|xargs kill -9", $masterPidArr);
    }

    protected static function start($config, $adapter, $appName)
    {
        self::staticRelealse($config['server']);
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

    /**
     * 静态文件发布
     * @param $staticPath
     * @param $staticMap
     * @param $staticCompilePath
     */
    protected static function staticRelealse($config)
    {
        $staticPath = rtrim(array_isset($config, "static_path"), "/");
        $staticCompilePath = array_isset($config, "static_public_path");

        $viewPath = isset($config['view']['path']) ? $config['view']['path'] : "";

        if (!$viewPath) {
            Log::error("server.httpd.server.view.path not set");
            return;
        }

        if (!$staticPath) {
            Log::error("server.httpd.server.static_path not set");
            return;
        }

        if (!$staticCompilePath) {
            Log::error("server.httpd.server.static_public_path not set");
            return;
        }

        $viewPath = Dir::formatPath($viewPath);
        $staticCompilePath = Dir::formatPath($staticCompilePath);

        $staticCompilePath = $staticCompilePath . "static/";
        if (!is_dir($staticCompilePath)) mkdir($staticCompilePath, 0777, true);

        $staticCompileExt = array_isset($config, "static_compile_ext");

        $exts = ["js", "css", "png", "gif", "jpg"];
        if (!$staticCompileExt) {
            $staticCompileExt = $exts;
        }

        $staticDir = basename($staticPath);

        $obj = new \Trensy\Mvc\View\Engine\Bladex\Asset();
        $staticPath = Dir::formatPath($staticPath);
        $obj->createDiffAsset($staticPath, $staticCompilePath, $staticCompileExt, $viewPath, $staticDir, [$staticPath.'ui/node_modules']);
    }

}