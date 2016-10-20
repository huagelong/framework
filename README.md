# a fast php framework

 include rpc server, web server, connection pool server

### 快速体验:

* 只支持linux

* 先安装 swoole,mbstring,posix扩展

* 根据 boilerplate 样板 安装 [see](https://github.com/trendi/boilerplate)

``
sudo composer require trendi/boilerplate
``

* 执行

```
sudo composer install

sudo chmod 0777 trendi

sudo ./trendi server:restart
```

* 在浏览器打开地址

``
http://127.0.0.1:7000/
``

* 支持 fis 前端工程构建工具, 需要安装 [nodejs](https://nodejs.org/en/), [npm](https://www.npmjs.com/), 安装教程请看各自官网

```
//安装fis
npm install -g fis3
//安装必要依赖
npm install
//启动服务器
sudo ./trendi server:restart
```

在浏览器 打开 http://127.0.0.1:7000/, 可以看到静态css,js 已经包含进去了

### 文档

[目录](doc/index.md)