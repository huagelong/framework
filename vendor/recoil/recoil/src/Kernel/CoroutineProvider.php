<?php

declare (strict_types = 1); // @codeCoverageIgnore

namespace Recoil\Kernel;

use Generator;

/**
 * An object that produces coroutines.
 */
interface CoroutineProvider
{
    public function coroutine() : Generator;
}
