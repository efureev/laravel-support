<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Test;

use Illuminate\Container\Container;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Php\Support\Helpers\Json;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

trait CreateHttpRequests
{
    protected array $serverParameters = [];

    /**
     * Additional headers for the request.
     *
     * @var array
     */
    protected array $defaultHeaders = [];

    /**
     * Additional cookies for the request.
     *
     * @var array
     */
    protected array $defaultCookies = [];

    /**
     * Additional cookies will not be encrypted for the request.
     *
     * @var array
     */
    protected array $unencryptedCookies = [];


    /**
     * Indicates whether cookies should be encrypted.
     *
     * @var bool
     */
    protected bool $encryptCookies = true;

    /**
     * Indicated whether JSON requests should be performed "with credentials" (cookies).
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/withCredentials
     *
     * @var bool
     */
    protected bool $withCredentials = false;

    protected function createGetRequest(string $uri, array $headers = []): Request
    {
        $server  = $this->transformHeadersToServerVars($headers);
        $cookies = $this->prepareCookiesForRequest();

        return $this->createRequest('GET', $uri, [], $cookies, [], $server);
    }

    protected function createGetJsonRequest(string $uri, array $headers = []): Request
    {
        return $this->createJsonRequest('GET', $uri, [], $headers);
    }


    protected function createPostJsonRequest(string $uri, array $data = [], array $headers = []): Request
    {
        return $this->createJsonRequest('POST', $uri, $data, $headers);
    }

    protected function createPostRequest(string $uri, array $data = [], array $headers = []): Request
    {
        $server  = $this->transformHeadersToServerVars($headers);
        $cookies = $this->prepareCookiesForRequest();

        return $this->createRequest('POST', $uri, $data, $cookies, [], $server);
    }

    protected function createDeleteRequest(string $uri, array $data = [], array $headers = []): Request
    {
        $server  = $this->transformHeadersToServerVars($headers);
        $cookies = $this->prepareCookiesForRequest();

        return $this->createRequest('DELETE', $uri, $data, $cookies, [], $server);
    }

    protected function createRequest(
        string $method,
        string $uri,
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

    protected function createJsonRequest(string $method, string $uri, array $data = [], array $headers = []): Request
    {
        $files   = $this->extractFilesFromDataArray($data);
        $content = Json::encode($data);

        $headers = array_merge([
            'CONTENT_LENGTH' => mb_strlen($content, '8bit'),
            'CONTENT_TYPE'   => 'application/json',
            'Accept'         => 'application/json',
        ], $headers);

        $cookies = $this->prepareCookiesForRequest();
        $server  = $this->transformHeadersToServerVars($headers);

        return $this->createRequest($method, $uri, [], $cookies, $files, $server, $content);
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

    protected function transformHeadersToServerVars(array $headers): array
    {
        return collect(array_merge($this->defaultHeaders, $headers))
            ->mapWithKeys(function ($value, $name) {
                $name = str_replace('-', '_', strtoupper($name));

                return [$this->formatServerHeaderKey($name) => $value];
            })
            ->all();
    }

    /**
     * If enabled, encrypt cookie values for request.
     *
     * @return array
     */
    protected function prepareCookiesForRequest(): array
    {
        if (!$this->encryptCookies) {
            return array_merge($this->defaultCookies, $this->unencryptedCookies);
        }

        return collect($this->defaultCookies)
            ->map(function ($value, $key) {
                $encrypter = Container::getInstance()->make('encrypter');
                $encPrefix = CookieValuePrefix::create($key, $encrypter->getKey());

                return $encrypter->encrypt($encPrefix . $value, false);
            })
            ->merge($this->unencryptedCookies)
            ->all();
    }

    /**
     * If enabled, add cookies for JSON requests.
     *
     * @return array
     */
    protected function prepareCookiesForJsonRequest(): array
    {
        return $this->withCredentials ? $this->prepareCookiesForRequest() : [];
    }


    /**
     * Format the header name for the server array.
     *
     * @param string $name
     *
     * @return string
     */
    protected function formatServerHeaderKey(string $name): string
    {
        if (!Str::startsWith($name, 'HTTP_') && $name !== 'CONTENT_TYPE' && $name !== 'REMOTE_ADDR') {
            return 'HTTP_' . $name;
        }

        return $name;
    }

    /**
     * Extract the file uploads from the given data array.
     *
     * @param array $data
     *
     * @return array
     */
    protected function extractFilesFromDataArray(array &$data): array
    {
        $files = [];

        foreach ($data as $key => $value) {
            if ($value instanceof SymfonyUploadedFile) {
                $files[$key] = $value;

                unset($data[$key]);
            }

            if (is_array($value)) {
                $files[$key] = $this->extractFilesFromDataArray($value);

                $data[$key] = $value;
            }
        }

        return $files;
    }
}
