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

namespace Trensy\Server\Swoole\Command\Httpd;

use Trensy\Config;
use Trensy\Foundation\Application;
use Trensy\Shortcut;
use Trensy\Server\Swoole\WSServer;
use Trensy\Support\AliasLoader;
use Trensy\Support\Arr;
use Trensy\Support\Dir;
use Trensy\Support\ElapsedTime;
use Trensy\Support\Exception;
use Trensy\Log;
use Trensy\Support\Tool;
use Trensy\Di;

class HttpdBase
{
    use Shortcut;
    protected static function taskAlias()
    {
        Di::set("task", ['class'=>\Trensy\Server\Swoole\Task::class]);
        AliasLoader::getInstance(['Task'=>\Trensy\Server\TaskFacade::class])->register();
    }

    public static function operate($cmd, $output, $input)
    {
        //定义task
        self::taskAlias();

        $config = Config::get("swoole.httpd");
        $appName = Config::get("app.app_name");

        $str = 'Welcome To Trensy!';
        Log::show($str);

        $tmpPath = "tmp path is : ".STORAGE_PATH;
        Log::show($tmpPath);

        if (!$appName) {
            Log::sysinfo("app.app_name not config");
            exit(0);
        }

        if (!$config) {
            Log::sysinfo("swoole.httpd config not config");
            exit(0);
        }

        if ($input->hasOption("daemonize")) {
            $daemonize = $input->getOption('daemonize');
            $daemonize = $daemonize == 0 ? 0 : 1;
            Config::set("swoole.httpd.daemonize", $daemonize);
        }

        try{
            self::doOperate($cmd);
        }catch (\Exception $e){
            Log::error(Exception::formatException($e));
        }

    }


    public static function doOperate($command)
    {
        $appName = Config::get("app.app_name");
        $serverName = $appName . "-httpd";

        $isRuning = self::isRuning();

        if ($command === 'start' && $isRuning) {
            Log::sysinfo("httpd server already running");
            return;
        }
        try{
            switch ($command) {
                case 'status':
                    if ($isRuning) {
                        Log::sysinfo("$serverName  already running");
                    } else {
                        Log::sysinfo("$serverName  not run");
                    }
                    break;
                case 'start':
                    $result = self::start();
                    if($result){
                        Log::sysinfo("$serverName start success ");
                    }else{
                        Log::sysinfo("$serverName start fail ");
                    }
                    break;
                case 'stop':
                    $result = self::stop();
                    if($result){
                        Log::sysinfo("$serverName stop success ");
                    }else{
                        Log::sysinfo("$serverName stop fail ");
                    }
                    break;
                case 'restart':
                    $result = self::stop();
                    if($result){
                        Log::sysinfo("$serverName stop success ");
//                        sleep(1);
                        $restartResult= self::start();
                        if($restartResult){
                            Log::sysinfo("$serverName start success ");
                            break;
                        }
                    }else{
                        Log::sysinfo("$serverName restart fail ");
                    }
                    break;
                case 'reload':
                    $result = self::reload();
                    if($result){
                        Log::sysinfo("$serverName reload success ");
                    }else{
                        Log::sysinfo("$serverName reload fail ");
                    }
                    break;
                default :
                    return "";
            }
        }catch (\Exception $e){
            Log::error($e->getMessage());
        }
    }
    

    protected static function reload()
    {
        $obj = new WSServer();
        return $obj->reload();
    }


    protected static function stop()
    {
        $obj = new WSServer();
        $result = $obj->stop();
        if(!$result) return false;
        $pfile = $obj->getPfile();
        if(is_file($pfile))  @unlink($pfile);
        return $result;
    }


    protected static function isRuning()
    {
        $obj = new WSServer();
        return $obj->isRunning();
    }

    protected static function start()
    {
        $config = Config::get("swoole.httpd");
        $swooleServer = new \swoole_websocket_server($config['host'], $config['port']);
        $obj = new WSServer($swooleServer);
        return $obj->start();
    }



}