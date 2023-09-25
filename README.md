<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii 2 Advanced Project Template</h1>
    <br>
</p>

Yii 2 Advanced Project Template is a skeleton [Yii 2](http://www.yiiframework.com/) application best for
developing complex Web applications with multiple tiers.

The template includes three tiers: front end, back end, and console, each of which
is a separate Yii application.

The template is designed to work in a team development environment. It supports
deploying the application in different environments.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-app-advanced.svg)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-app-advanced.svg)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![build](https://github.com/yiisoft/yii2-app-advanced/workflows/build/badge.svg)](https://github.com/yiisoft/yii2-app-advanced/actions?query=workflow%3Abuild)

DIRECTORY STRUCTURE
-------------------

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
    scripts/             contains project releted scripts
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
api
    config/              contains api configurations
    controllers/         contains Web controller classes
    models/              contains api-specific model classes
    runtime/             contains files generated during runtime
    web/                 contains the entry script and Web resources
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```



## I. Start cronjobs</h1>

1. Create file `/console/scripts/instance-local.conf` containing the following variables:

    * **INSTANCE_NAME**: name of the instance ( `levtech` | `ghallard` )
    * **DBHOST**: the database host
    * **MYSQL_USER**: user to connect to database
    * **MYSQL_PASSWORD**: password for database
    * **SQL_PATH**: where to save the database backups
    * **IGNORE_DATABASES**: databases to ignore at backup
    * **IGNORE_TABLES**: tables to ignore at backup
    ___
    **Example:**
    ```
        INSTANCE_NAME="levtech"
        DBHOST="db"
        MYSQL_USER="root"
        MYSQL_PASSWORD="pass"
        SQL_PATH="/mnt/erp-hourly/data/db_backups"
        IGNORE_DATABASES="'information_schema','mysql','performance_schema','phpmyadmin','sys'"
        IGNORE_TABLES=("ecf_adm.log")
    ```

1. Create `console/log` directory for cronjobs logs

    ```
    mkdir console/log
    ```

1. Copy with sudo `console/scripts/get_modules_cron`  in `/etc/crod.d/`

    ```bash
    sudo cp console/scripts/get_modules_cron /etc/crod.d/get_modules_cron
    ```

1. To check if cron started successfuly look in /var/log/syslog for  `CRON` keyword 

    ```
    less /var/log/syslog | grep CRON
    ```

1. To view cron output check `console/log/get_modules_cron.log`
