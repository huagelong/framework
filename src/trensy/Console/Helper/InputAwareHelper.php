<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Trensy\Console\Helper;

use Trensy\Console\Input\InputInterface;
use Trensy\Console\Input\InputAwareInterface;

/**
 * An implementation of InputAwareInterface for Helpers.
 *
 * @author Wouter J <waldio.webdesign@gmail.com>
 */
abstract class InputAwareHelper extends Helper implements InputAwareInterface
{
    protected $input;

    /**
     * {@inheritdoc}
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }
}
