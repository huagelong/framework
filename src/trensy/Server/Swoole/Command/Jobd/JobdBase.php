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

namespace Trensy\Server\Swoole\Command\Jobd;

use Trensy\Config;
use Trensy\Foundation\Application;
use Trensy\Shortcut;
use Trensy\Server\Swoole\JobdServer;
use Trensy\Support\AliasLoader;
use Trensy\Support\Arr;
use Trensy\Support\Dir;
use Trensy\Support\ElapsedTime;
use Trensy\Support\Exception;
use Trensy\Log;
use Trensy\Support\Tool;
use Trensy\Di;

class JobdBase
{
    use Shortcut;
    protected static function taskAlias()
    {
        Di::set("task", ['class'=>\Trensy\Server\Swoole\JobTask::class]);
        AliasLoader::getInstance(['Task'=>\Trensy\Server\TaskFacade::class])->register();
    }

    public static function operate($cmd, $output, $input)
    {
        //定义task
        self::taskAlias();

        $config = Config::get("swoole.jobd");
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
            Log::sysinfo("swoole.jobd config not config");
            exit(0);
        }

        if ($input->hasOption("daemonize")) {
            $daemonize = $input->getOption('daemonize');
            $daemonize = $daemonize == 0 ? 0 : 1;
            Config::set("swoole.jobd.daemonize", $daemonize);
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
        $serverName = $appName . "-jobd";

        $isRuning = self::isRuning();

        if ($command === 'start' && $isRuning) {
            Log::sysinfo("jobd server already running");
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
                        sleep(1);
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
        $obj = new JobdServer();
        return $obj->reload();
    }

    protected static function stop()
    {
        $obj = new JobdServer();
        $result = $obj->stop();
        if(!$result) return false;
        $pfile = $obj->getPfile();
        if(is_file($pfile))  @unlink($pfile);
        return true;
    }


    protected static function isRuning()
    {
        $obj = new JobdServer();
        return $obj->isRunning();
    }

    protected static function start()
    {
        $config = Config::get("swoole.jobd");
        $swooleServer = new \swoole_server($config['host'], $config['port']);
        $obj = new JobdServer($swooleServer);
        return $obj->start();
    }



}