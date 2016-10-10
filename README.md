# a fast php framework

 include rpc server, web server, connection pool server

### 快速体验:

* 安装

``
composer require trendi/framework:master-dev
``

* 设置

vendor/trendi/framework/tests/application/config/share/storage.php

里面的pdo和redis连接

* 打开

vendor/trendi/framework/tests/application/trendi

修改代码

``
require_once __DIR__ . "/../../vendor/autoload.php";
``

使其正确导入 vendor/autoload.php 文件

* 设置完成后执行

``
php vendor/trendi/framework/tests/application/trendi server:restart
``

* 在浏览器打开地址

http://127.0.0.1:7000/index/index/trendi