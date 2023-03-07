## 零依后台管理系统

基于高性能HTTP服务框架webman，内置权限管理、角色管理、用户管理、日志管理等基础功能。

## 安装
根目录 `.env` 修改数据库连接参数，命令行执行 `php cli install`(可选参数 `-u` 用户名 `-p` 密码) 等待安装完成即可。
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

## 启动

系统以常驻内存方式运行，需要在项目根目录下通过命令行启动

`windows`: 
1.  `php win.php` 安装模块时请通过该方式启动
2. `php win.php dev` 开发调试时通过该方式启动，文件修改时自动重启

`linux`: 
1.  `php start.php start -d`
2. `php start.php start`

访问后台：http://127.0.0.1:8787/system

## 官网

https://www.yiadmin.net

## LICENSE
MIT
