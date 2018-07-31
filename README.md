# ApePHP

## 简述

**ApePHP**
第一个开源作品，想法很简单，为什么不能有一个属于自己的框架呢，一个自己随心所欲的框架，所以有了ApePHP。
 虽说是第一个开源作品但可能也是最后一个PHP开源作品，放弃它只是为寻找我所追求的，但还是会继续深究学习PHP的哈哈哈
 学开发有一段时间了，其实接触接触一个框架底层还是比较有意思的，这样会让你更加深刻更加理解一个框架是怎么来的， 在写这个框架的同时也吸收了一些免费开源框架的优秀设计思想，本人技术浅薄，望各位大牛们刀下留情，放小的一马，有建议可以多提提哈哈哈。
ApePHP是一个免费开源的，简单的面向对象的轻量级PHP开发框架，源码只有几百KB，遵循Apache2开源许可协议发布。
详细介绍请参考网站：http://apephp.xcpei.com 。

环境要求：

* PHP 5.4.0+
* PDO PHP Extension

## 目录说明

```
project               根目录
├─apephp                框架核心目录 
│  ├─library               框架核心目录
│  │  ├─db                数据库封装类目录
│  │  ├─*.php             框架核心类
│  ├─base.php              框架常量配置文件
│  ├─helper.php            框架系统函数文件
│  ├─start.php             框架启动文件
├─application           应用目录
│  ├─module                模块目录
│  │  ├─controlle         控制器目录
│  │  ├─model             模型目录
│  │  ├─view              视图目录
│  ├─common.php            公共函数文件
│  ├─config.php            配置文件
│  ├─database.php          数据库配置文件
│  ├─extra_config.php      扩展配置文件
│  ├─route.php             路由配置文件
├─extend                项目扩展目录
├─public                静态文件目录
├─runtime               项目运行时目录
├─index.php             入口文件
```

## 使用

### 1.克隆代码

```
git clone https://github.com/xiaocp/apephp.git
git clone https://git.oschina.net/xiaocp/apephp.git
```

### 2.配置Nginx或Apache
在Apache或Nginx中创建一个站点，把 project 设置为站点根目录（入口文件 index.php 所在的目录）。

然后设置单一入口， Apache服务器配置：
```
<IfModule mod_rewrite.c>
    # 打开Rerite功能
    RewriteEngine On

    # 如果请求的是真实存在的文件或目录，直接访问
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d

    # 如果访问的文件或目录不是真事存在，分发请求至 index.php
    RewriteRule . index.php
</IfModule>
```
Nginx服务器配置：
```
location / {
    # 重新向所有非真是存在的请求到index.php
    try_files $uri $uri/ /index.php$args;
}
```

### 3.测试访问

然后访问站点域名：http://domain/ 就可以了。
