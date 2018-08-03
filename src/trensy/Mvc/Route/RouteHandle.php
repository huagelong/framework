<?php
/**
 * User: Peter Wang
 * Date: 16/12/9
 * Time: 下午10:15
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
                    $usesTmp = strpos($uses, "#")?substr($uses,0,strpos($uses, "#")):$uses;
                    list($modules, $controller, $action) = explode("/", $usesTmp);

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

                    $realUses = "\\App\\Modules\\" . ucwords($modules) . "\\Controller\\" . $controllerStr."Controller@" . $action;

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