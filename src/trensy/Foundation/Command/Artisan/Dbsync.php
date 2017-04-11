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

class Dbsync extends Base
{
    private $tableName = null;

    protected function configure()
    {
        //InputArgument
        $this->setName('db:sync')
            ->addOption('--config', '-c', InputOption::VALUE_OPTIONAL, 'sync database config')
            ->addOption('--sqldir', '-d', InputOption::VALUE_OPTIONAL, 'sql file dir path')
            ->addOption('--prefix', '-p', InputOption::VALUE_OPTIONAL, 'database table prefix')
            ->addOption('--action', '-a', InputOption::VALUE_OPTIONAL, 'database migration action', "up")
            ->setDescription('database sync project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $storageConfig = config()->get("storage.server.pdo");
        $inputConfig = $input->getOption("config");
        $inputConfig = $inputConfig?$inputConfig:$storageConfig;

        $sqlpath = $input->getOption("sqldir");
        $sqlpath = $sqlpath?$sqlpath:APPLICATION_PATH."/sql/";

        $prefix = $input->getOption("prefix");
        $prefix = $prefix?$prefix:$storageConfig['sync_prefix'];

        $action = $input->getOption("action");

        $newPrefix = $inputConfig['prefix'];
        $this->tableName = "{$newPrefix}dbsync";

        //判断表格是否存在
        $db = new Pdo($inputConfig);
        $sql =  "SHOW TABLES like '{$this->tableName}'";
        $checkData = $db->fetch($sql);

        if(!$checkData){
            $sql = "CREATE TABLE `{$this->tableName}` ( `id` INT NOT NULL AUTO_INCREMENT , `filename` VARCHAR(100) NOT NULL DEFAULT '', `ftype` varchar(5) NULL DEFAULT 'up',`created_at` TIMESTAMP NULL , `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci";
            $db->exec($sql);
            Log::sysinfo("dbSync initialize success!");
        }

        $this->importDb($inputConfig, $sqlpath, $prefix, $action);

        Log::sysinfo("sync completed!");
    }

    protected function getImportFilePath($sqlpath, $db, $action)
    {
        if($action == 'down'){
            $diffTime = $db->getField("created_at", [], false, "", "id DESC", "", "", "",'dbsync');
            if(!$diffTime) return ;
            $diffFile = $db->getField("created_at", ["created_at"=>$diffTime], true, "", "id DESC", "", "", "",'dbsync');
            return [$diffFile];
        }

        $importFileNames = $db->getField("filename", [], true, "", "", "", "", "",'dbsync');

        $this->getFiles($sqlpath, $files);

        if(!$files) return ;

        $newfile = [];
        foreach ($files as $v){
            $tmpNewFile = substr(str_replace($sqlpath, "", $v),0,-4);
            if(stristr($tmpNewFile, "_")){
                list($tmpFileName, $actionType) = explode("_", $tmpNewFile);
                if($actionType == 'up'){
                    $newfile[] = $tmpFileName;
                }
            }
        }

        $diffFile = array_diff($newfile, $importFileNames);

        if(!$diffFile) return ;

        asort($diffFile);

        return $diffFile;
    }

    protected function getFiles($sqlpath, &$files)
    {
        $handle = dir($sqlpath);
        $source = Dir::formatPath($sqlpath);
        while ($entry = $handle->read()) {
            if (($entry != ".") && ($entry != "..")) {
                if (is_dir($source . $entry)) {
                    $this->getFiles($sqlpath, $files);
                } else {
                    if(pathinfo($entry, PATHINFO_EXTENSION) == 'sql') $files[] = $source . $entry;
                }
            }
        }
    }

    protected function importDb($inputConfig, $sqlpath, $prefix, $action)
    {
        $db = new Pdo($inputConfig);

        $newPrefix = $inputConfig['prefix'];
        $prefix = $prefix?$prefix:$newPrefix;

        $files = $this->getImportFilePath($sqlpath, $db, $action);
        if(!$files){
            Log::sysinfo("no sql need import");
            return ;
        }
        $createAt = date('Y-m-d H:i:s');
        foreach ($files as $v){
            $filePath = $sqlpath.$v."_".$action.".sql";
            Log::sysinfo("start import :". $filePath);
            $db->import($filePath, $prefix, $newPrefix, [$this->tableName]);
            $insertData = [];
            $insertData['filename'] = $v;
            $insertData['created_at'] = $createAt;
            $insertData['ftype'] = $action;
            $insertData['updated_at'] = date('Y-m-d H:i:s');
            $db->insert($insertData, "dbsync");
        }
    }


}