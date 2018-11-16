<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 2018/11/16
 * Time: 11:18
 */

namespace Trensy\Foundation\Command\Migrations;
use Symfony\Component\Console\Helper\HelperSet;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Trensy\Config;

class Base
{

    public static function getConfig()
    {
        $dbConfig = Config::get('storage.server.pdo');
        $params = array(
            'dbname' => $dbConfig['master']['db_name'],
            'user' => $dbConfig['master']['user'],
            'password' => $dbConfig['master']['password'],
            'host' => $dbConfig['master']['host'],
            'port' => $dbConfig['master']['port'],
            'charset' => 'utf8',
            'driver' => 'pdo_'.$dbConfig['type'],
        );
        $conn = \Doctrine\DBAL\DriverManager::getConnection($params);

        $dir = ROOT_PATH."/resources/migrations";
        if(!is_dir($dir)) mkdir($dir, 0777, true);
        $configuration = new Configuration($conn);
        $configuration->setMigrationsTableName("base_migrations");
        $configuration->setMigrationsDirectory($dir);
        $configuration->setMigrationsNamespace('Database\\Migrations');
        $configuration->registerMigrationsFromDirectory($dir);
        return $configuration;
    }
}