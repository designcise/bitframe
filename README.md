[![codecov](https://codecov.io/gh/designcise/bitframe/branch/master/graph/badge.svg?token=7V77L5P3AX)](https://codecov.io/gh/designcise/bitframe)
[![Build Status](https://travis-ci.com/designcise/bitframe.svg?branch=master)](https://travis-ci.com/designcise/bitframe)

# BitFrame PHP Microframework

A highly customizable PSR-15 / PSR-7 compatible middleware-based microframework for PHP that comes bundled with a simple PSR-11 based DI container for sharing services and data across the application. It is:

1. Easy-to-learn and intuitive;
2. Standards-based;
4. Simple by design;
5. Free of unnecessary bloat;
6. Non-intrusive;
7. Customizable, modular and easy-to-scale.

## How to Get Started?

You can get started in a few simple steps:

1. Setup your environment;
2. Install `composer` dependencies;
3. Create your first "Hello World" app.

Also, please note the following prerequisites:

### Prerequisites

1. PHP 8.1+;
2. Server with URL Rewriting (such as Apache, Nginx, etc.).

### 1. Setup Your Environment

You can refer to the following minimal Apache and Nginx configurations to get started:

#### Apache

Create an `.htaccess` file with at least the following code:

```apacheconfig
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

This sets the directive in Apache to redirect all Http requests to a front controller `index.php` file in which you can write your main application code.

#### Nginx

A configuration like the following in Nginx will help you set the directive to rewrite path to your application front controller (i.e. `index.php`):

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

### 2. Install Composer Dependencies

You can use `composer require` like so:

```
$ composer require "designcise/bitframe":^3.5
```

Or, alternatively, you can add the package dependency in your `composer.json` file.

Please note that you must include a PSR-17 factory in your composer dependencies. [nyholm/psr7](https://github.com/Nyholm/psr7/blob/master/src/Factory/Psr17Factory.php) and [guzzle/psr7](https://github.com/guzzle/psr7/blob/master/src/HttpFactory.php) are good examples of these &mdash; if you include either of these, they're automatically picked up by BitFrame. For any other PSR-17 factory implementation, you can add it add via `\BitFrame\Factory\HttpFactory::addFactory()` method before you instantiate `\BitFrame\App`.

### 3. Create Your First "Hello World" App

If you have ever used a middleware based framework, you will feel at ease. A "Hello World" app would look something like the following:

```php
<?php

require 'vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use BitFrame\App;
use BitFrame\Emitter\SapiEmitter;

// 1. Instantiate the App
$app = new App();

$middleware = function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
    $response = $handler->handle($request);
    $response->getBody()->write('Hello World!');
    return $response;
};

// 2. Add middleware
$app->use([
    SapiEmitter::class,
    $middleware,
]);

// 3. Run the application
$app->run();
```

From the code above you can see that the application uses two middlewares: 

1. A PSR-15 middleware `\BitFrame\Emitter\SapiEmitter` (that comes bundled with BitFrame package) which allows to emit the HTTP Response to the requesting user-agent (such as a web browser);
2. A closure middleware used to write `Hello World!` to the HTTP response stream.

This is of course a very basic example. In a real-world application, the setup would be much more complex than this. For example, you could extend the functionality by using an additional PSR-15 middleware such as a [router](https://github.com/designcise/bitframe-fastroute), [error handler](https://github.com/designcise/bitframe-whoops), etc. For suggestions on how to go about designing your application (and to get started quickly), have a look at the [simple dockerized boilerplate example](https://github.com/designcise/bitframe-boilerplate).

## Tests

To run the tests you can use the following commands:

| Command             | Type             |
| ------------------- |:----------------:|
| `composer test`     | PHPUnit tests    |
| `composer style`    | CodeSniffer      |
| `composer style-fix`| CodeSniffer Fixer|
| `composer md`       | MessDetector     |
| `composer check`    | PHPStan          |

### Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

* File issues at https://github.com/designcise/bitframe/issues
* Issue patches to https://github.com/designcise/bitframe/pulls

### License

Please see [License File](LICENSE.md) for licensing information.
