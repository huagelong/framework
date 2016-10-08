<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Trendi\Mvc\Route\Base\Matcher\Dumper;

use Trendi\Mvc\Route\Base\RouteCollection;

/**
 * MatcherDumper is the abstract class for all built-in matcher dumpers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class MatcherDumper implements MatcherDumperInterface
{
    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * Constructor.
     *
     * @param RouteCollection $routes The RouteCollection to dump
     */
    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
