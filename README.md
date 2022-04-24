## 零依后台管理系统

基于高性能HTTP服务框架webman，内置权限管理、角色管理、用户管理、日志管理等基础功能。

## 安装
根目录 `.env` 修改数据库连接参数，命令行执行 `php webman install`(可选参数 `-u` 用户名 `-p` 密码) 等待安装完成即可。
```
APP_DEBUG = false
SQL_LOG = false
DB_NAME = mysql
DB_DRIVER = mysql
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_DATABASE = 
DB_USERNAME = root
DB_PASSWORD = 
DB_COLLATION = utf8mb4_general_ci
DB_CHARSET = utf8mb4
DB_PREFIX = yi_
SERVER_LISTEN = http://0.0.0.0:8787
CHANNEL_PORT = 2206
```
访问后台：http://127.0.0.1:8787/system

## 官网

https://www.01soft.top

## LICENSE
MIT
