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
