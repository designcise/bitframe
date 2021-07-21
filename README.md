# BitFrame PHP Microframework

[![codecov](https://codecov.io/gh/designcise/bitframe/branch/master/graph/badge.svg)](https://codecov.io/gh/designcise/bitframe)
[![Build Status](https://travis-ci.com/designcise/bitframe.svg?branch=master)](https://travis-ci.com/designcise/bitframe)

## At-a-glance

* Highly customizable PSR-15 and PSR-7 compatible middleware-based microframework for PHP;
* Simple PSR-11 based dependency injection container;
* Simple to learn, use and implement;
* Follows the [PSR standards](http://www.php-fig.org/) and integrates the best of existing opensource frameworks wherever possible.

## Why use BitFrame?

BitFrame's approach of making the middleware dispatcher the core component of the framework encourages the developer to use middleware-based services that plug right-in with ease. This allows for greater flexibility especially in terms of debugging, replacements and updating. While this design pattern may not suit the needs of all projects, it certainly has its advantages for long-term and strategic ones because as they grow in complexity the underlying framework helps keep things well-organized, easy-to-manage and very simple.

At the core of our development, we've tried very hard to abide by some simple rules that we've mostly found missing in other microframeworks:

1. **Easy-to-learn:** Be well-documented and intuitive;
1. **Non-intrusive:** Facilitate the developer and not be a nuisance;
1. **Simple by design:** Encourage flow of development to be simple and easy to read;
1. **Customizable:** Promote modularity and high customizability;
1. **Fat-free:** Be free of unnecessary bloat;
1. **Standards-based:** Be standards-based wherever possible.

## Installation

Install BitFrame and its required dependencies using composer:

```
$ composer require "designcise/bitframe"
```

Please note that BitFrame v3+ requires PHP 8.0.0 or newer.

## Quickstart

Get started quickly by using the boilerplate code at https://github.com/designcise/bitframe-boilerplate.

### Apache

After you have installed the required dependencies specific to your project, create an `.htaccess` file with at least the following code:

```apacheconfig
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

This sets the directive in apache to redirect all Http Requests to `index.php` in which we can write our application code.

### Nginx

A configuration like the following in nginx will help you set the directive to rewrite path to our application front controller (i.e. `index.php`):

```
server {
  listen 80;
  server_name 127.0.0.1;

  root /var/www/html/public;
  index index.php;

  location / {
    try_files $uri $uri/ /index.php$is_args$args;
  }

  location ~ \.php$ {
    fastcgi_split_path_info ^(.+\.php)(/.+)$;
    fastcgi_pass app:9000;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param PATH_INFO $fastcgi_path_info;
  }

  access_log /var/log/nginx/access.log;
  error_log  /var/log/nginx/error.log;
}
```

Remember to make changes according to your project setup. For example, ensure that `listen`, `root`, `fastcgi_pass`, `*_log`, etc. are setup correctly according to your project.

### Example

For a full application example, please [check out the boilerplate](https://github.com/designcise/bitframe-boilerplate).

```php
<?php

require 'vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use BitFrame\App;
use BitFrame\Emitter\SapiEmitter;

$app = new App();

$middleware = function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
    $response = $handler->handle($request);
    $response->getBody()->write('Hello World!');
    return $response;
};

$app->use([
    SapiEmitter::class,
    $middleware,
]);

$app->run();
```

From the code above you can see that we're using two middlewares: 

1. A PSR-15 middleware `\BitFrame\Emitter\SapiEmitter` that allows us to emit the HTTP Response to the requesting user-agent (such as a web browser);
1. A closure middleware used to write `Hello World!` to the HTTP Response.

This is of course a very basic example. You could extend the functionality by using additional middleware such as a [router](https://github.com/designcise/bitframe-fastroute), error handler, etc.

## Tests

To run the tests you can use the following commands:

| Command          | Type            |
| ---------------- |:---------------:|
| `composer test`  | PHPUnit tests   |
| `composer style` | CodeSniffer     |
| `composer md`    | MessDetector    |
| `composer check` | PHPStan         |

### Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

* File issues at https://github.com/designcise/bitframe/issues
* Issue patches to https://github.com/designcise/bitframe/pulls

### Documentation

Complete documentation for v3 will be released soon.

### License

Please see [License File](LICENSE.md) for licensing information.
