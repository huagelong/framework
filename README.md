# a fast php framework

 include rpc server, web server

### 快速体验:

* 只支持linux, 以下以ubuntu为例

* 先安装或者开启swoole,mbstring,posix扩展

* 更好体验建议安装 apc ,msgpack 扩展, 安装apc提速30%

* 执行下面命令

```

sudo composer create-project --prefer-dist trensy/trensy

cd trensy

sudo composer install

sudo chmod 0777 trensy

sudo chmod -R 0777 storage

sudo php trensy server:restart
```

* 在浏览器打开地址

``
http://127.0.0.1:7000/
``


* 欢迎大家发起pull request, 一起完善项目.

[文档](https://github.com/trensy/doc)