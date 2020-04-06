# BitFrame PHP Microframework

[![Coverage Status](https://coveralls.io/repos/github/designcise/bitframe/badge.svg?branch=2.x)](https://coveralls.io/github/designcise/bitframe?branch=2.x)
[![Build Status](https://travis-ci.org/designcise/bitframe.svg?branch=2.x)](https://travis-ci.org/designcise/bitframe)

## About

* Highly customizable PSR-15 and PSR-7 compatible middleware-based microframework for PHP;
* Simple to learn, use and implement;
* Follows the [PSR standards](http://www.php-fig.org/) and integrates the best of existing opensource frameworks wherever possible.

## Why use BitFrame?

BitFrame's approach of making the middleware dispatcher the core component of the framework encourages the developer to use middleware-based services that plug right-in with ease. This allows for greater flexibility especially in terms of debugging, replacements and updating. While this design pattern may not suit the needs of all projects, it certainly has its advantages for long-term and strategic ones because as they grow in complexity the underlying framework helps keep things well-organized, easy-to-manage and very simple.

At the core of our development, we've tried very hard to abide by some simple rules that we've mostly found missing in other microframeworks:

1. Be well-documented and intuitive;
1. Facilitate the developer and be nonintrusive;
1. Be free of unnecessary bloat;
1. Promote modularity to allow any component of the framework to be easily replaced;
1. Provide the flexibility of using existing PSR-15 / PSR-7 middlewares that plug right in easily;
1. Provide the ability to share variables and application data seamlessly across all the middlewares.

## Installation

Install BitFrame and its required dependencies using composer:

```
$ composer require "designcise/bitframe:2.x-dev"
```

Please note that BitFrame v2+ requires PHP 7.4.0 or newer.

## Boilerplate

Available at: https://github.com/designcise/bitframe-boilerplate.

## Quickstart

### Apache

After you have installed the required dependencies specific to your project, create an `.htaccess` file with at least the following code:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

This sets the directive in apache to redirect all Http Requests to `index.php` in which we can write our application code. For example:

```
<?php

require 'vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use BitFrame\App;
use BitFrame\Emitter\SapiEmitter;

$app = new App();

$app->use([
    SapiEmitter::class,
    function (
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ) {
        $handler->write('Hello World!');
        return $handler->handle($request);
    }
]);

$app->run();
```

From the code above you can see that we're using two middlewares: 

1. A PSR-15 middleware `\BitFrame\Emitter\SapiEmitter` that allows us to emit the HTTP Response to the requesting user-agent (such as a web browser);
1. A closure middleware used to write `Hello World!` to the HTTP Response.

This is of course a very basic example. You could extend the functionality by using additional middleware such as a [router](https://github.com/designcise/bitframe-fastroute/tree/2.x), error handler, etc.

### Tests

To execute the test suite, you will need [PHPUnit](https://phpunit.de/). To run the tests simply use one of the following composer commands:

```
composer unit
composer integration
composer test
```

### Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

* File issues at https://github.com/designcise/bitframe/issues
* Issue patches to https://github.com/designcise/bitframe/pulls

### Documentation

Complete documentation for v2.0 will be released soon.

### License

Please see [License File](LICENSE.md) for licensing information.
