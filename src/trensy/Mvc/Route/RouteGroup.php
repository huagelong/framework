<?php
/**
 * goup 处理
 * 
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Mvc\Route;

use Trensy\Foundation\Shortcut;
use Trensy\Mvc\Route\Base\RouteCollection as BaseRouteCollection;
use Trensy\Support\Arr;

class RouteGroup
{
    use Shortcut;
    protected $name = "";
    protected $prefix = "";
    protected $defaults = [];
    protected $domain = "";
    protected $methods = [];
    protected static $result = [];
    protected static $groupHash = null;
    protected static $groupPrefixs = null;

    public static function getGroupPrefixs()
    {
        return self::$groupPrefixs;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }


    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function setMiddleware($middleware)
    {
        $this->defaults = Arr::merge($this->defaults, ['_middleware' => $middleware]);
    }

    public function setMethods($methods)
    {
        $this->methods = $methods;
    }

    public static function getResult()
    {
        return self::$result;
    }

    public static function getGroupHash()
    {
        return self::$groupHash;
    }

    public function bind($closure)
    {
        $subCollection = new BaseRouteCollection();
        $key = "group_" . spl_object_hash($subCollection);

        self::$groupHash = $key;
        //reset
        RouteBase::clearResult($key);

        call_user_func($closure);

        $result = RouteBase::getResult($key);

        if (!$result) return [];
        
        foreach ($result as $k => $v) {
            $name = $this->name ."@".$k;
//            $name = $k;
            $subCollection->add($name, $v);
        }
        
        if($this->prefix) self::$groupPrefixs[] = $this->prefix;

        $subCollection->addPrefix($this->prefix);
        $subCollection->mergeDefaults($this->defaults);
        $subCollection->addRequirements([]);
        $subCollection->setHost($this->domain);
        $subCollection->setMethods($this->methods);

        return self::$result[$key] = $subCollection;
    }


    public function __destruct()
    {
    }

}