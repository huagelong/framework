<?php
/**
 * User: Peter Wang
 * Date: 16/9/22
 * Time: 下午2:08
 */

namespace Trendi\Test\Dao;


class UserDao extends Base
{
    public $tableName = "user";

    public function test()
    {
        $data = $this->selectRow("SELECT * FROM mall_user LIMIT 10");
        return $data;
    }
}