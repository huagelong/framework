<?php
/**
 * StatusCommand
 *
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Foundation\Command\Migrations;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class StatusCommand extends \Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand
{
    public function execute(InputInterface $input, OutputInterface $output){
//        $this->setHelperSet(Base::getHelpSet());
        $this->setMigrationConfiguration(Base::getConfig());
        parent::execute($input, $output);
    }
}