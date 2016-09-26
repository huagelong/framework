<?php
/**
 * User: Peter Wang
 * Date: 16/9/14
 * Time: 下午6:28
 */

namespace Trendi\Server;


class Reload
{

    public static function load($serverName, $rate)
    {
        if (self::check($rate)) {
            return;
        } else {
            dump("Memory is full ,will restart!");
        }

        $serverName = $serverName . "-http-server";
        exec("ps axu|grep " . $serverName . "$|awk '{print $2}'", $serverPidArr);
        $masterPid1 = $serverPidArr ? current($serverPidArr) : null;
        if ($masterPid1) {
            posix_kill($masterPid1, SIGUSR1);
//            posix_kill($masterPid1, SIGUSR2);
        }

        $serverName = $serverName . "-pool-server";
        exec("ps axu|grep " . $serverName . "$|awk '{print $2}'", $serverPidArr);
        $masterPid2 = $serverPidArr ? current($serverPidArr) : null;
        if ($masterPid2) {
            posix_kill($masterPid2, SIGUSR1);
        }

        $serverName = $serverName . "-rpc-server";
        exec("ps axu|grep " . $serverName . "$|awk '{print $2}'", $serverPidArr);
        $masterPid3 = $serverPidArr ? current($serverPidArr) : null;
        if ($masterPid3) {
            posix_kill($masterPid3, SIGUSR1);
        }
    }

    protected static function check($rate)
    {
        $mem = self::getMemory();
        $memoryLimit = ini_get("memory_limit");
        dump("Memory:" . $mem . "M/" . $memoryLimit . "-[" . date('Y-m-d H:i:s') . "]");
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