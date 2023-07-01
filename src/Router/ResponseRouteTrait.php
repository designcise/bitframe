<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2023 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Router;

use Psr\Http\Message\ResponseInterface;
use BitFrame\Http\Message\{
    DownloadResponse,
    FileResponse,
    HtmlResponse,
    JsonpResponse,
    JsonResponse,
    RedirectResponse,
    TextResponse,
    XmlResponse,
};

trait ResponseRouteTrait
{
    use RouterTrait;

    public function text(array|string $methods, string $route, string $text, int $statusCode = 200): void
    {
        $this->map(
            (array) $methods,
            $route,
            static fn () => (new TextResponse($text))->withStatus($statusCode)
        );
    }

    public function html(array|string $methods, string $route, string $html, int $statusCode = 200): void
    {
        $this->map(
            (array) $methods,
            $route,
            static fn (): ResponseInterface => (new HtmlResponse($html))->withStatus($statusCode)
        );
    }

    public function json(array|string $methods, string $route, array $data, int $statusCode = 200): void
    {
        $this->map(
            (array) $methods,
            $route,
            static fn (): ResponseInterface => (new JsonResponse($data))->withStatus($statusCode)
        );
    }

    public function jsonp(
        array|string $methods,
        string $route,
        array $data,
        string $callback,
        int $statusCode = 200,
    ): void {
        $this->map(
            (array) $methods,
            $route,
            static fn (): ResponseInterface => (new JsonpResponse($data, $callback))->withStatus($statusCode)
        );
    }

    public function xml(array|string $methods, string $route, string $xml, int $statusCode = 200): void
    {
        $this->map(
            (array) $methods,
            $route,
            static fn (): ResponseInterface => (new XmlResponse($xml))->withStatus($statusCode)
        );
    }

    public function file(string $route, string $filePath): void
    {
        $this->map(
            ['GET'],
            $route,
            static fn (): ResponseInterface => new FileResponse($filePath)
        );
    }

    public function download(
        string $route,
        string $downloadUrl,
        string $serveFilenameAs = '',
    ): void {
        $this->map(
            ['GET'],
            $route,
            static fn (): ResponseInterface => new DownloadResponse($downloadUrl, $serveFilenameAs)
        );
    }

    public function redirect(string $fromUrl, string $toUrl, int $statusCode = 302): void
    {
        $this->map(
            ['GET'],
            $fromUrl,
            static fn (): ResponseInterface => new RedirectResponse($toUrl, $statusCode)
        );
    }
}
