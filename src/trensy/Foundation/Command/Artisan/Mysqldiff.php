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

namespace Trensy\Foundation\Command\Artisan;

use Trensy\Console\Input\InputArgument;
use Trensy\Console\Input\InputInterface;
use Trensy\Console\Output\OutputInterface;
use Trensy\Console\Input\InputOption;
use Trensy\Foundation\Command\Base;
use Trensy\Foundation\Storage\Pdo;
use Trensy\Support\Dir;
use Trensy\Support\Log;

class Mysqldiff extends Base
{
    protected function configure()
    {
        $this->setName('mysql:diff')
            ->addArgument("type", InputArgument::OPTIONAL, 'schema or data or all - Specifies the type of diff to do either on the schema, data or both. schema is the default', 'schema')
            ->setDescription('db diff, eg: mysql:diff or mysql:diff all or mysql:diff data or mysql:diff schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (!$this->checkCmd("mysqldbcompare")) {
                throw  new \Exception("mysqldbcompare command not found");
            }

            $type = $input->getArgument("type");
            $type = $type ? $type : "schema";

            $this->todo($type);

            Log::show("done");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    protected function todo($type)
    {
        $bin = $this->whichBin("mysqldbcompare");

        if(!$this->config()->get("storage.server.diff.master")){
            throw  new \Exception("config storge.server.diff.master not found");
        }

        $server1 = $this->config()->get("storage.server.pdo.master");
        $dbServer1Str = $server1['user'] . ":" . $server1['password'] . "@" . $server1['host'] . ":" . $server1['port'];

        $server2 = $this->config()->get("storage.server.diff.master");
        $dbServer2Str = $server2['user'] . ":" . $server2['password'] . "@" . $server2['host'] . ":" . $server2['port'];



        if (!in_array($type, ['schema', 'data', 'all'])) {
            throw  new \Exception("type must 'schema or data or all '");
        }

        $includeArr = ["up", "down"];
        $outputPath = $this->config()->get("storage.diff_output");
        $cmdArr = [];
        $cmdArr[] = "--server1=".$dbServer1Str;
        $cmdArr[] = "--server2=".$dbServer2Str;
        $cmdArr[] = "--difftype=sql";
        $cmdArr[] = "--run-all-tests";
        $cmdArr[] = "--compact";
        $cmdArr[] = "--skip-table-options";
        $cmdArr[] = "--quiet";

        if($type == 'schema'){
            $cmdArr[] = "--skip-data-check";
        }elseif($type == 'data'){
            $cmdArr[] = "--skip-diff";
        }

        $cmdArr[] = $server1['db_name'] . ":" . $server2['db_name'];

        $cmdOptionDefault  = implode(" ", $cmdArr);


        $outputPath = Dir::formatPath($outputPath);

        foreach ($includeArr as $v) {
            $now = date('YmdHis');
            $cmdArr = [];

            $outputFilePath = $outputPath . $now . "_" . $v . ".sql";

            if($v == 'up'){
                $tableSql = $this->diffCreate("storage.server.pdo", "storage.server.diff");
                $cmdArr[] = "--changes-for=server2";
                $old_prefix="`".$server2['db_name']."`.";
                $new_prefix="";
            }else{
                $tableSql = $this->diffCreate("storage.server.diff", "storage.server.pdo");
                $cmdArr[] = "--changes-for=server1";
                $old_prefix="`".$server1['db_name']."`.";
                $new_prefix="";
            }

            $tableSqlStr = implode("\n\n", $tableSql);

            $cmdOptionSTr  = implode(" ", $cmdArr);

            $cmdSTr = $bin." ". $cmdOptionSTr . " ". $cmdOptionDefault;

            $result = $this->exec($cmdSTr);
            Log::show("RUN [" . $cmdSTr . "] Success!");

            $resultArr = $this->clearSql($result, $old_prefix, $new_prefix);

            if(!$resultArr){
                $resultStr = $tableSqlStr;
            }else{
                $resultArrTmp = [];
                $prefix = $this->config()->get("storage.server.pdo.prefix");
                $tableSync = $prefix."dbsync";
                if($resultArr){
                    foreach ($resultArr as $rv){
                        if(strstr($rv, $tableSync)) continue;
                        $resultArrTmp[] = $rv;
                    }
                }
                if($tableSqlStr) $tableSqlStr .= "\n\n";
                $resultStr = $tableSqlStr.implode("", $resultArrTmp);
            }
            if($resultStr) file_put_contents($outputFilePath, $resultStr);
        }


        $cmd = $this->whichBin("php") ." ". ROOT_PATH."/trensy db:sync --config storage.server.diff";
        $resultArr =$this->exec($cmd);
        Log::show("RUN [" . $cmd . "] Success!");
        $resultStr = implode("\n", $resultArr);

        Log::show($resultStr);

        return $tableSqlStr;
    }

    protected function diffCreate($server1Config, $server2Config)
    {
        $server1Config = $this->config()->get($server1Config);
        $server2Config = $this->config()->get($server2Config);

        $server1Pdo = new Pdo($server1Config);
        $server2Pdo = new Pdo($server2Config);

        $sql =  "SHOW TABLES";
        $server1Data = $server1Pdo->fetchAll($sql);
        $server2Data = $server2Pdo->fetchAll($sql);
        $server1Data = $this->arrChange($server1Data);
        $server2Data = $this->arrChange($server2Data);
//        $this->dump($server1Data);
//        $this->dump($server2Data);

        $prefix = $this->config()->get("storage.server.pdo.prefix");
        $tableSync = $prefix."dbsync";

        $diff1 = [];
        $diff2 = [];
        if($server1Data){
            if(!$server2Data){
                $diff1 = $server1Data;
            }else{
                foreach ($server1Data as $v){
                    if($v == $tableSync) continue;
                    if(!in_array($v, $server2Data)){
                        $diff1[]=$v;
                    }
                }
            }
        }


        if($server2Data){
            if(!$server1Data){
                $diff2 = $server2Data;
            }else{
                foreach ($server2Data as $v){
                    if($v == $tableSync) continue;
                    if(!in_array($v, $server1Data)){
                        $diff2[]=$v;
                    }
                }
            }
        }


        $resultSql = [];
        if($diff1){
            foreach ($diff1 as $v){
                $sql =  "SHOW CREATE TABLE `".$v."`";
                $tableData = $server1Pdo->fetch($sql);
                if($tableData){
                    $resultSql[] = $tableData['Create Table'].";";
                }
            }
        }

        if($diff2){
            foreach ($diff2 as $v){
                $sql =  "DROP TABLE  `".$v."`;";
                $resultSql[] = $sql;
            }
        }

        return $resultSql;
    }

    protected function arrChange($arr)
    {
       if(!$arr) return $arr;
       $result = [];
       foreach ($arr as $v){
           $result[] = current($v);
       }
       return $result;
    }

    protected function checkCmd($cmd)
    {
        $cmdStr = "command -v ".$cmd;
        exec($cmdStr, $check);
        if(!$check){
            return false;
        }else{
            return current($check);
        }
    }

    /**
     * 查询命令全部路径
     * @param $str
     * @return bool
     */
    protected function whichBin($str){
        exec("which {$str}", $result);
        if($result) return current($result);
        return false;
    }

    protected function exec($command){
        exec($command , $outRs, $rs);
        return $outRs?$outRs:false;
    }

    /*
		参数：
		$old_prefix:原表前缀；
		$new_prefix:新表前缀；
	*/
    function clearSql($segment,$old_prefix="",$new_prefix="")
    {
        $commenter = array('#','-','\/\*', '\+', '\@', 'WARNING','\[');
        //去掉注释和多余的空行
        $data=array();
        foreach($segment as  $statement)
        {
            $sentence = explode("\n",$statement);
            $newStatement = array();
            foreach($sentence as $subSentence)
            {
                if('' != trim($subSentence))
                {
                    //判断是会否是注释
                    $isComment = false;
                    foreach($commenter as $comer)
                    {
                        if(preg_match("/^(".$comer.")/is",trim($subSentence)))
                        {
                            $isComment = true;
                            break;
                        }
                    }
                    //如果不是注释，则认为是sql语句
                    if(!$isComment)
                        $newStatement[] = $subSentence;
                }
            }
            $data[] = $newStatement;
        }
        $result = [];
        //组合sql语句
        foreach($data as  $statement)
        {
            $newStmt = '';
            foreach($statement as $sentence)
            {
                $newStmt = $newStmt.trim($sentence)."\n";
            }
            if(!empty($newStmt))
            {
                $newStmt = str_replace(array($old_prefix, "\r", ";"), array($new_prefix, "\n", ";\n"), $newStmt);//替换前缀
                $result[] = $newStmt;
            }
        }
        return $result;
    }

}
