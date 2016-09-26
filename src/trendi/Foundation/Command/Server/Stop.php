<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Server;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Stop extends Command
{
    protected function configure()
    {
        $this
            ->setName('server:stop')
            ->setDescription('stop all server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ServerBase::operate("stop", $output, $input);
    }
}
