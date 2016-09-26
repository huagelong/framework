<?php
/**
 * User: Peter Wang
 * Date: 16/9/22
 * Time: 下午2:08
 */

namespace Trendi\Test\Dao;

class UserWxDao extends Base
{
    public $tableName = "user_wx";

    public function test()
    {
        $data = $this->selectRow("SELECT LAST_INSERT_ID() as lastid");
//        $data = "hello";
        return $data;
    }
}