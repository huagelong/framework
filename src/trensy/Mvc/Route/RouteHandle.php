<?php
/**
 * 路由分配
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

namespace Trensy\Mvc\Route;


use Trensy\Shortcut;

class RouteHandle
{
    use Shortcut;
    public function perform($configAll)
    {
        foreach ($configAll as &$config) {
            $routes = $this->array_isset($config, 'routes');
            if ($routes) {
                $newRoutes = [];
                foreach ($routes as $v) {
                    $method = $this->array_isset($v, 0);
                    $path = $this->array_isset($v, 1);
                    $uses = $this->array_isset($v, 2);
                    $middleware = $this->array_isset($v, 3);
                    $tmp = [];
                    $tmp['method'] = $method;
                    $modules = strpos($uses, "::")?substr($uses,0,strpos($uses, "::")):$uses;
                    $usesTmp = strpos($uses, "::")?substr($uses,strpos($uses, "::")+2):$uses;
                    list($controller, $action) = explode("/", $usesTmp);

                    $controllerTmp = explode("@", $controller);
                    $controllerGroup = $this->array_isset($controllerTmp,0);
                    if(count($controllerTmp)>=2){
                        $controllerArrTmp = array_map(function($v){
                            return ucwords($v);
                        },$controllerTmp);
                        $controllerStr = implode("\\", $controllerArrTmp);
                    }else{
                        $controllerStr = ucwords($controllerGroup);
                    }

                    $realUses = "\\".ucwords($modules)."\\Controllers\\" . $controllerStr."Controller@" . $action;

                    $tmp['path'] = $path;
                    $tmp['uses'] = $realUses;
                    $tmp['name'] = $uses;
                    $tmp['middleware'] = $middleware;
                    $newRoutes[] = $tmp;
                }
                $config['routes'] = $newRoutes;
            }
        }
        return $configAll;
    }
}