<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Foundation\Command;

use Trensy\Foundation\Bootstrap\Bootstrap;
use Trensy\Console\Command\Command;
use Trensy\Foundation\AnnotationLoadInterface;
use Trensy\Shortcut;

class Base extends Command implements AnnotationLoadInterface
{
    use Shortcut;

  public function __construct()
  {
      ini_set('output_buffering', 0);
      ini_set('implicit_flush', 1);
      ob_implicit_flush(1);

      parent::__construct();
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