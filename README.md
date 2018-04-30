# BitFrame PHP Microframework

* Highly customizable and event driven PSR-15 and PSR-7 compatible middleware microframework for PHP;
* Simple to learn, use and implement;
* Follows the [PSR standards](http://www.php-fig.org/) and integrates the best of existing opensource frameworks wherever possible.

### Why use BitFrame?

BitFrame's approach of making the middleware dispatcher the core component of the framework encourages the developer to use middleware-based services that plug right-in with ease. This allows for greater flexibility especially in terms of debugging, replacements and updating. While this design pattern may not suit the needs of all projects, it certainly has its advantages for long-term and strategic ones because as they grow in complexity the underlying framework helps keep things well-organized, easy-to-manage and very simple.

At the core of our development, we've tried very hard to abide by some simple rules that we've mostly found missing in other microframeworks:

1. Be well-documented and intuitive;
1. Facilitate the developer and be nonintrusive;
1. Be free of unnecessary bloat;
1. Promote modularity to allow any component of the framework to be easily replaced;
1. Provide the flexibility of using existing PSR-15 / PSR-7 middlewares that plug right in easily;
1. Provide the ability to share variables and application data seamlessly across all the middlewares.

### Installation

Install BitFrame and its required dependencies using composer:

```
$ composer require designcise/bitframe
```

Please note that BitFrame requires PHP 7.1.0 or newer.

### Quickstart

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

$app = new \BitFrame\Application();

$app->addMiddleware([
    \BitFrame\Message\DiactorosResponseEmitter::class,
	function ($request, $response, $next) {
		$response->getBody()->write('Hello World!');

		return $next($request, $response);
	}
]);

$app->run();
```

From the code above you can see that we're using two middlewares: 

1. A PSR-15 based wrapper of [Zend Diactoros](https://github.com/zendframework/zend-diactoros) that allows us to emit the HTTP Response to the requesting user-agent (such as a web browser);
1. A closure used to write `Hello World!` to the HTTP Response.

Similar to the `\BitFrame\Message\DiactorosResponseEmitter` middleware package [we've developed some essential PSR-15 packages](https://www.bitframephp.com/doc/getting-started/install#how-to-install-project-dependencies-middlewares) that are typically used in API and web app development. These can be added to BitFrame easily as per your project's need and are in no way a hard dependency.

Also note that, apart from these official packages, you can easily use any PSR-15 or PSR-7 based packages, or develop your own.

Refer to the [official BitFrame documentation](https://www.bitframephp.com/doc/) to learn more.

### Tests

To execute the test suite, you will need [PHPUnit](https://phpunit.de/).

### Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

* File issues at https://github.com/designcise/bitframe/issues
* Issue patches to https://github.com/designcise/bitframe/pulls

### Documentation

Documentation is available at:

* https://www.bitframephp.com/doc/

### License

Please see [License File](LICENSE.md) for licensing information.
