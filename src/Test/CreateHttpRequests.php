<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Test;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

trait CreateHttpRequests
{
    protected array $serverParameters = [];

    protected function createRequest(
        string $uri,
        string $method,
        array $parameters = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $content = null
    ): Request {
        $symfonyRequest = SymfonyRequest::create(
            static::prepareUriForRequest($uri),
            $method,
            $parameters,
            $cookies,
            $files,
            array_replace($this->serverParameters, $server),
            $content
        );

        return Request::createFromBase($symfonyRequest);
    }

    protected static function prepareUriForRequest(string $uri): string
    {
        if (Str::startsWith($uri, '/')) {
            $uri = substr($uri, 1);
        }

        return trim(url($uri), '/');
    }

    public function withServerParameters(array $server): static
    {
        $this->serverParameters = $server;

        return $this;
    }
}
