<?php
/**
 * 根据内存使用比率自动重启服务器
 *
 * User: Peter Wang
 * Date: 16/9/14
 * Time: 下午6:28
 */

namespace Trendi\Server;

use Trendi\Support\Log;

class Reload
{

    public static function load($serverName, $rate, $showLog=false)
    {
        if(!$rate) return ;
        
        if (self::check($rate, $showLog)) {
            return;
        } else {
            Log::warn("Memory is full ,will restart!");
        }

        exec("ps axu|grep " . $serverName . "$|awk '{print $2}'", $serverPidArr);
        $masterPid = $serverPidArr ? current($serverPidArr) : null;
        if ($masterPid) {
            posix_kill($masterPid, SIGUSR1);
        }
    }

    protected static function check($rate, $showLog)
    {
        $mem = self::getMemory();
        $memoryLimit = ini_get("memory_limit");
        if($showLog) Log::sysinfo("Memory:" . $mem . "M/" . $memoryLimit);
        if ($memoryLimit == '-1') return true;
        $memoryLimitUnmber = substr($memoryLimit, 0, -1);

        if (strtolower(substr($memoryLimit, -1)) == 'g') {
            $memoryLimit = $memoryLimitUnmber * 1024;
        } elseif (strtolower(substr($memoryLimit, -1)) == 't') {
            $memoryLimit = $memoryLimitUnmber * 1024 * 1024;
        } else {
            $memoryLimit = $memoryLimitUnmber;
        }
        return ($mem / $memoryLimit) > $rate ? false : true;
    }


    public static function getMemory()
    {
        return round(memory_get_usage() / 1024 / 1024, 2);
    }


}