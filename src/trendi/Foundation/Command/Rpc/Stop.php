<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Rpc;

use Trendi\Console\Command\Command;
use Trendi\Console\Input\InputInterface;
use Trendi\Console\Output\OutputInterface;

class Stop extends Command
{
    protected function configure()
    {
        $this
            ->setName('rpc:stop')
            ->setDescription('stop the rpc server ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        RpcBase::operate("stop", $output, $input);
    }
}
