<?php

namespace Katapoka\Ahgora\Adapters;

use GuzzleHttp\Client;
use Katapoka\Ahgora\IHttpClient;

/**
 * Guzzle IHttpClient version
 */
class GuzzleAdapter implements IHttpClient
{
    /** @var \GuzzleHttp\Client */
    private $client;

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
     * @return \Katapoka\Ahgora\HttpResponse
     */
    public function request($method, $url, $data = array(), array $config = array())
    {
        // TODO: Implement request() method.
    }

    /**
     * Make a get request to an URL.
     *
     * @param string $url
     * @param array  $data
     * @param array  $config
     *
     * @return \Katapoka\Ahgora\HttpResponse
     */
    public function get($url, $data = array(), array $config = array())
    {
        // TODO: Implement get() method.
    }

    /**
     * Make a post request to an URL.
     *
     * @param string $url
     * @param array  $data
     * @param array  $config
     *
     * @return \Katapoka\Ahgora\HttpResponse
     */
    public function post($url, $data = array(), array $config = array())
    {
        // TODO: Implement post() method.
    }

    /**
     * Set a header to the request.
     *
     * @param string $header
     * @param string $value
     *
     * @return \Katapoka\Ahgora\IHttpClient the instance of the class for method chaining
     */
    public function setHeader($header, $value)
    {
        // TODO: Implement setHeader() method.
    }

    /**
     * Retrieves the value of a given header name.
     *
     * @param string $header
     *
     * @return string
     */
    public function getHeader($header)
    {
        // TODO: Implement getHeader() method.
    }

    /**
     * Set a timeout to the connection.
     *
     * @param int $ttl
     *
     * @return \Katapoka\Ahgora\IHttpClient
     */
    public function setTimeout($ttl)
    {
        // TODO: Implement setTimeout() method.
    }
}
