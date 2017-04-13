<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Foundation\Command;

use Trensy\Foundation\Bootstrap\Bootstrap;
use Trensy\Console\Command\Command;
use Trensy\Foundation\Shortcut;

class Base extends Command
{
    use Shortcut;

  public function __construct()
  {
      parent::__construct();
      Bootstrap::getInstance(ROOT_PATH);
  }

    /**
     * @param $cmdName
     * @return \Trensy\Console\Input\InputDefinition
     */
    public function getCmdDefinition($cmdName)
    {
        $result = $this->getApplication()->find($cmdName);
        return $result->getDefinition();
    }
}