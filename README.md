# a fast php framework

 include rpc server, web server, connection pool server

### 快速体验:

* 安装

``
composer require trendi/framework:master-dev
``

* 设置

tests/application/config/share/storage.php

里面的pdo和redis连接


* 安装成功后执行

``
php tests/application/trendi server:restart
``

* 在浏览器打开地址

http://127.0.0.1:7000/index/index/trendi