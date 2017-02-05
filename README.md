Demo API for Symfony 3
========================

[Install Vagrant and Homestead](https://symfony.com/doc/current/setup/homestead.html)

## Installation

```sh
$ git clone https://github.com/sger/demo_api
$ cd demo_api
$ homestead up
$ homestead ssh
$ cd demo_api
$ composer Install
$ php bin/console doctrine:database:drop --force
$ php bin/console doctrine:database:create
$ php bin/console doctrine:schema:update --force
$ php bin/console doctrine:fixtures:load
```

Add the following line to your hosts and edit homestead.yml with your project directory path.

  192.168.10.10 demo-api.app

```sh
curl -H "X-AUTH-TOKEN: test123456" http://demo-api.app/logger
curl -H "X-AUTH-TOKEN: test123456" http://demo-api.app/user/1
curl -H "X-AUTH-TOKEN: test123456" http://demo-api.app/user/2
```
