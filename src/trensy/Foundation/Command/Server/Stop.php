<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trensy\Foundation\Command\Server;

use Trensy\Console\Input\InputInterface;
use Trensy\Console\Output\OutputInterface;
use Trensy\Foundation\Command\Base;

class Stop extends Base
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
