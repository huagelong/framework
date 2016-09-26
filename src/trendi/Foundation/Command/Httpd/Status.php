<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Httpd;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Status extends Command
{
    protected function configure()
    {
        $this
            ->setName('httpd:status')
            ->setDescription('show http server status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        HttpdBase::operate("status", $output, $input);
    }
}
