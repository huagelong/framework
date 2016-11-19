<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trensy\Foundation\Command\Artisan;

use Trensy\Console\Input\InputInterface;
use Trensy\Console\Input\InputOption;
use Trensy\Console\Output\OutputInterface;
use Trensy\Console\Input\InputArgument;
use Trensy\Foundation\Command\Base;
use Trensy\Support\Log;

class Clean extends Base
{
    protected function configure()
    {
        $this->setName('clean')
            ->setDescription('clean project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
    }
}
