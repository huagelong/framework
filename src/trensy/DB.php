<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 2018/7/29
 * Time: 19:37
 */

namespace Trensy;


use Trensy\DaoAbstract;

class DB extends DaoAbstract
{
    protected $alias = [];  //记录全局的语句参数

    public static function conn(){
        return new self();
    }

    //where语句
    public function where($key, $compute, $value=null, $join = "AND")
    {
        $compute = strtolower($compute);

        if($value === null){
            $tmp = [$compute, null, $join];
        }else{
            if(in_array($compute, ["=","!=",">",">=","<","<=","in"])){
                $tmp = [$compute, $value, $join];
            }else{
                return $this;
            }
        }

        if(isset($this->alias['where'][$key])){
            array_push($this->alias['where'][$key], $tmp);
        }else {
            $this->alias['where'][$key] = [$tmp];
        }
        return $this;
    }

    public function table($table)
    {
        $this->alias['table'] = $table;
        return $this;
    }

    public function isNull($key)
    {
        $this->where($key, "IS NULL");
        return $this;
    }

    public function isNotNull($key)
    {
        $this->where($key, "IS NOT NULL");
        return $this;
    }

    public function whereNeq($key, $value)
    {
        $this->where($key, "!=", $value);
        return $this;
    }

    public function whereEq($key, $value)
    {
        $this->where($key, "=", $value);
        return $this;
    }

    public function whereIn($key, $value)
    {
        $this->where($key, "in", $value);
        return $this;
    }

    //limit语句
    public function limit($limit)
    {
        $this->alias['limit'] = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->alias['offset'] = $offset;
        return $this;
    }

    //order语句
    public function orderBy($order)
    {
        $this->alias['order'] = $order;
        return $this;
    }

    //group语句
    public function groupBy($group)
    {
        $this->alias['group'] = $group;
        return $this;
    }

    /**
     * 查询多条
     * @return mixed
     */
    public function find()
    {
        $where = isset($this->alias['where'])?$this->alias['where']:[];
        $order = isset($this->alias['order'])?$this->alias['order']:'';
        $group = isset($this->alias['group'])?$this->alias['group']:'';
        $limit = isset($this->alias['limit'])?$this->alias['limit']:'';
        $offset = isset($this->alias['offset'])?$this->alias['offset']:'';
        $table = isset($this->alias['table'])?$this->alias['table']:'';

        return $this->gets($where, $order, $limit, $offset, $group, false, $table);
    }

    //查询一条
    public function findOne()
    {
        $where = isset($this->alias['where'])?$this->alias['where']:[];
        $order = isset($this->alias['order'])?$this->alias['order']:'';
        $group = isset($this->alias['group'])?$this->alias['group']:'';
        $table = isset($this->alias['table'])?$this->alias['table']:'';

        return $this->get($where, $order,$group, $table);
    }

    /**
     * 删除
     * @return mixed
     */
    public function remove()
    {
        $where = isset($this->alias['where'])?$this->alias['where']:[];
        $table = isset($this->alias['table'])?$this->alias['table']:'';

        return $this->delete($where, $table);
    }
}