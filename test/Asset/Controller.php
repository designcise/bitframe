<?php

namespace BitFrame\Test\Asset;

use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\RequestHandlerInterface;
use BitFrame\Router\Route;

class Controller
{
    public function __construct(private readonly string $foo = 'bar')
    {}

    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        string $foo = 'bar'
    ): ResponseInterface {
        return self::staticAction($request, $handler, $foo);
    }

    #[Route(['GET', 'POST'], '/test')]
    #[Route('POST', '/test-2')]
    public function indexAction(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        echo $this->foo;
        return $handler->handle($request);
    }

    #[Route(['GET'], '/test2')]
    public function methodAction(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        return self::staticAction($request, $handler);
    }

    #[Route('PUT', '/static-method')]
    public static function staticAction(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        echo 'bar';
        return $handler->handle($request);
    }
}
