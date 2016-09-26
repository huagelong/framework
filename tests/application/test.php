<?php
$process = new swoole_process('callback_function', true);
$pid = $process->start();

function callback_function(swoole_process $worker)
{
    $worker->exec('php',
        [
            "/mnt/hgfs/code/eletron_trendy/trendi/tests/application/trendi.php",
            "httpd:start"
        ]);
}

swoole_process::wait();