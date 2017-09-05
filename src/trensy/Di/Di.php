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

namespace Trensy\Di;

use Trensy\Di\Exception\DiNotDefinedException;

class Di
{
    /**
     *  Container instance
     *
     */
    protected static $containerInstance = null;


    /**
     * @return Container
     */
    public static function getContainer()
    {
        if (!self::$containerInstance) {
            self::$containerInstance =new Container();
        }
        return self::$containerInstance;
    }

    /**
     * register Container
     *
     * @param $name
     * @param $options
     * @param bool $isShare
     * @param bool $isLazy
     * @return mixed
     */
    private static function register($name,$definition=[],$params=[], $shared = true)
    {
        if (!$name) {
            throw new DiNotDefinedException(" container object is not found ~");
        }

        if(!$definition){
            $definition['class'] = $name;
        }else{
            if(is_array($definition) && !isset($definition['class'])){
                $definition['class'] = $name;
            }
        }

        if($shared){
            return self::getContainer()->setSingleton($name, $definition, $params);
        }else{
            return self::getContainer()->set($name, $definition, $params);
        }
    }

    /**
     * setting Container
     *
     * @param $name
     * @param $options
     * @return mixed
     */
    public static function set($name, $definition=[],$params=[])
    {
        return self::register($name, $definition,$params,false);
    }

    /**
     * @param $name
     * @return object
     * @throws DiNotDefinedException
     */
    public static function get($name, $params=[], $config=[])
    {
        $service = self::getContainer()->get($name, $params, $config);
        if (!$service) {
            throw new DiNotDefinedException(" Container is not defined ~");
        }
        return $service;
    }

    /**
     * 是否存在
     *
     * @param $name
     * @return bool
     */
    public static function has($name)
    {
        $service = self::getContainer()->has($name);
        return $service?true:false;
    }

    /**
     * @param $name
     * @param array $definition
     * @param array $params
     * @return mixed
     */
    public static function shareSet($name, $definition=[],$params=[])
    {
        return self::register($name, $definition,$params,true);
    }


    public function __destruct()
    {
    }
}