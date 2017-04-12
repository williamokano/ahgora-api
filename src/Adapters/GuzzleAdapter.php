<?php

namespace Katapoka\Ahgora\Adapters;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use GuzzleHttp\Client;
use Katapoka\Ahgora\HttpResponse;
use Katapoka\Ahgora\Contracts\IHttpClient;

/**
 * Guzzle IHttpClient version
 */
class GuzzleAdapter implements IHttpClient
{
    /** @var \GuzzleHttp\Client The guzzle client. */
    private $client;

    /** @var int the timeout of the client or the request. */
    private $timeout;

    /** @var array The client headers. */
    private $headers = [];

    /** @var bool Set if the request sends an json */
    private $isJson = false;

    /**
     * GuzzleAdapter constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Make an http request to some URL with the given http method
     *
     * @param string $method
     * @param string $url
     * @param array  $data
     * @param array  $config
     *
     * @return \Katapoka\Ahgora\Contracts\IHttpResponse
     */
    public function request($method, $url, $data = [], array $config = [])
    {
        $config = array_merge($config, [
            'form_params' => $data,
            'headers'     => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $response = $this->client->request($method, $url, $config);

        $httpResponse = new HttpResponse([
            'httpStatus' => $response->getStatusCode(),
            'body'       => $response->getBody(),
            'headers'    => $response->getHeaders(),
        ]);

        return $httpResponse;
    }

    /**
     * Make a get request to an URL.
     *
     * @param string $url
     * @param array  $data
     * @param array  $config
     *
     * @return \Katapoka\Ahgora\Contracts\IHttpResponse
     */
    public function get($url, $data = [], array $config = [])
    {
        return $this->request(IHttpClient::HTTP_GET, $url, $data, $config);
    }

    /**
     * Make a post request to an URL.
     *
     * @param string $url
     * @param array  $data
     * @param array  $config
     *
     * @return \Katapoka\Ahgora\Contracts\IHttpResponse
     */
    public function post($url, $data = [], array $config = [])
    {
        return once(function () use ($url, $data, $config) {
            return $this->request(IHttpClient::HTTP_POST, $url, $data, $config);
        });
    }

    /**
     * Set a header to the request.
     *
     * @param string $header
     * @param string $value
     *
     * @return \Katapoka\Ahgora\Contracts\IHttpClient the instance of the class for method chaining
     */
    public function setHeader($header, $value)
    {
        if (!is_string($header)) {
            throw new InvalidArgumentException('Header should be a string');
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('Value should be a string');
        }

        $this->headers[$header] = $value;

        return $this;
    }

    /**
     * Unset a header to the request.
     *
     * @param string $header
     *
     * @return \Katapoka\Ahgora\Contracts\IHttpClient the instance of the class for method chaining
     */
    public function unsetHeader($header)
    {
        if ($this->headerExists($header)) {
            unset($this->headers[$header]);
        }

        return $this;
    }

    /**
     * Retrieves the value of a given header name.
     *
     * @param string $header
     *
     * @return string|null
     */
    public function getHeader($header)
    {
        if ($this->headerExists($header)) {
            return $this->headers[$header];
        }

        return null;
    }

    /**
     * Get all headers from the http client.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set a timeout to the connection.
     *
     * @param int $timeout
     *
     * @return \Katapoka\Ahgora\Contracts\IHttpClient
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Set if the request will response a json instead of form data.
     *
     * @param bool $isJson
     *
     * @return \Katapoka\Ahgora\Contracts\IHttpClient
     */
    public function setIsJson($isJson = true)
    {
        if (!is_bool($isJson)) {
            throw new InvalidArgumentException('IsJson should be a boolean');
        }

        $this->isJson = $isJson;
        if ($isJson) {
            $this->setHeader('content-type', 'application/json');
        } else {
            $this->unsetHeader('content-type');
        }

        return $this;
    }

    private function headerExists($header)
    {
        return array_key_exists($header, $this->headers);
    }
}
