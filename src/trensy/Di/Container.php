<?php
/**
 * User: Peter Wang
 * Date: 17/3/20
 * Time: 下午5:01
 */

namespace Trensy\Di;

use Trensy\Di\Exception\InvalidArgumentException;

class Container
{
    protected $instance = [];
    protected $name = null;
    protected $ContainerData = [];

    public function has($name)
    {
        return isset($this->instance[$name]);
    }

    public function get($name)
    {
        if (!isset($this->ContainerData[$name]) || !$this->ContainerData[$name]) {
            throw new InvalidArgumentException("Container not exist!");
        }

        list($class, $params, $isShare, $relates) = $this->ContainerData[$name];
        
        if($isShare && isset($this->instance[$name])){
            return $this->instance[$name];
        }
        
        $class = new \ReflectionClass($class);
        $object = $class->newInstanceArgs($params);

        //对对象进行初始化.对象都是应用
        if($relates){
            foreach ($relates as $relate){
                if (!is_callable($relate)) {
                    throw new InvalidArgumentException(sprintf('The configure callable for class "%s" is not a callable.', get_class($object)));
                }
                call_user_func($relate, $object);
            }
        }

        if ($isShare && $object !== null) {
            $this->instance[$name] = $object;
        }
        return $object;
    }


    public function set($name, $class, $params=[], $isShare=true, $relates=[])
    {
        if (isset($this->ContainerData[$name])) {
            return true;
        }
        $this->ContainerData[$name] = [$class, $params, $isShare, $relates];
        return true;
    }
}