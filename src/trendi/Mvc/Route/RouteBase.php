<?php
/**
 * User: Peter Wang
 * Date: 16/9/10
 * Time: 下午4:50
 */

namespace Trendi\Mvc\Route;

use Symfony\Component\Routing\Route as SymfonyRoute;
use Trendi\Support\Arr;

class RouteBase
{
    const DEFAULTGROUP_KEY = "group_default";

    protected $path = null;
    protected $defaults = [];
    protected $requirements = [];
    protected $host = "";
    protected $methods = [];
    protected $name = null;
    protected static $result = [];
    protected $resultKey = null;
    /**
     * @var \Symfony\Component\Routing\Route
     */
    protected $routeObj = null;

    public function where($key, $value = "")
    {
        if (is_array($key)) {
            $this->requirements = Arr::merge($this->requirements, $key);
        }

        if (is_string($key)) {
            $tmp = [$key => $value];
            $this->requirements = Arr::merge($this->requirements, $tmp);
        }

        $this->routeObj->setRequirements($this->requirements);

        $this->setResult($this->resultKey, $this->routeObj);

        return $this;
    }

    public function name($key)
    {
        $this->name = $key;

        $preKey = self::DEFAULTGROUP_KEY;

        if (RouteGroup::getGroupHash()) {
            $preKey = RouteGroup::getGroupHash();
        }

        unset(self::$result[$preKey][$this->resultKey]);

        $this->setResult($this->name, $this->routeObj);
        $this->resultKey = $this->name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function defaults(array $default)
    {
        $this->defaults = Arr::merge($this->defaults, $default);

        $this->routeObj->setDefaults($this->defaults);

        $this->setResult($this->resultKey, $this->routeObj);

        return $this;
    }

    public function middleware()
    {

        $numargs = func_num_args();
        $argList = func_get_args();

        if ($numargs == 0) return;

        $tmp = [];
        for ($i = 0; $i < $numargs; $i++) {
            if (is_array($argList[$i])) {
                $tmp = Arr::merge($tmp, $argList[$i]);
            } else {
                $tmp[] = $argList[$i];
            }
        }

        $default = ['_middleware' => $tmp];

        $this->defaults($default);

        return $this;
    }

    public function domain($host)
    {
        $this->host = $host;

        $this->routeObj->setHost($this->host);

        $this->setResult($this->resultKey, $this->routeObj);

        return $this;
    }


    public function match($methods, $path, $closureOrArr)
    {
        if (!is_array($methods)) $methods = array($methods);

        $this->path = $path;
        $this->methods = $methods;

        if (!is_array($closureOrArr)) {
            $this->defaults['_controller'] = $closureOrArr;
        }

        $route = new SymfonyRoute(
            $this->path, // path
            $this->defaults, // default values
            $this->requirements, // requirements
            [], // options
            $this->host, // host
            [], // schemes
            $this->methods // methods
        );

        $this->routeObj = $route;
        $this->resultKey = spl_object_hash($route);

        $this->setResult($this->resultKey, $route);

        return $this;
    }

    protected function setResult($key, $value)
    {
        $preKey = self::DEFAULTGROUP_KEY;

        if (RouteGroup::getGroupHash()) {
            $preKey = RouteGroup::getGroupHash();
        }
        self::$result[$preKey][$key] = $value;
    }


    public static function getResult($key = '')
    {
        if (!$key) {
            return self::$result;
        }
        return isset(self::$result[$key]) ? self::$result[$key] : [];
    }

    public static function clearResult($key = self::DEFAULTGROUP_KEY)
    {
        unset(self::$result[$key]);
    }

    public function __destruct()
    {
    }

}