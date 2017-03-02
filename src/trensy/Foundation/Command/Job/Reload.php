<?php
/**
 * Created by PhpStorm.
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Foundation\Command\Job;

use Trensy\Console\Input\InputInterface;
use Trensy\Console\Output\OutputInterface;
use Trensy\Foundation\Command\Base;

class Reload extends Base
{
    protected function configure()
    {
        $this
            ->setName('job:reload')
            ->setDescription('reload the job server ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        JobBase::operate("reload", $output, $input);
    }
}
