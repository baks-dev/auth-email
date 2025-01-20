# BaksDev Auth Email

[![Version](https://img.shields.io/badge/version-7.2.0-blue)](https://github.com/baks-dev/auth-email/releases)
![php 8.3+](https://img.shields.io/badge/php-min%208.3-red.svg)

Модуль авторизации пользователя по E-mail

## Установка

``` bash
$ composer require baks-dev/auth-email
```

## Дополнительно

Установка конфигурации и файловых ресурсов:

``` bash
$ php bin/console baks:assets:install
```

Изменения в схеме базы данных с помощью миграции

``` bash
$ php bin/console doctrine:migrations:diff

$ php bin/console doctrine:migrations:migrate
```

## Тестирование

``` bash
$ php bin/phpunit --group=auth-email
```

## Лицензия ![License](https://img.shields.io/badge/MIT-green)

The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.

