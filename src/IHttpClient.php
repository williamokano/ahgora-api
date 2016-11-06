<?php

namespace Katapoka\Ahgora;

/**
 * Interface responsible for defining the default contract (instead of psr-7)
 * of how to make http requests.
 */
interface IHttpClient
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';

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
    public function request($method, $url, $data = [], array $config = []);

    /**
     * Make a get request to an URL.
     *
     * @param string $url
     * @param array  $data
     * @param array  $config
     *
     * @return \Katapoka\Ahgora\HttpResponse
     */
    public function get($url, $data = [], array $config = []);

    /**
     * Make a post request to an URL.
     *
     * @param string $url
     * @param array  $data
     * @param array  $config
     *
     * @return \Katapoka\Ahgora\HttpResponse
     */
    public function post($url, $data = [], array $config = []);

    /**
     * Set a header to the request.
     *
     * @param string $header
     * @param string $value
     *
     * @return \Katapoka\Ahgora\IHttpClient the instance of the class for method chaining
     */
    public function setHeader($header, $value);

    /**
     * Unset a header to the request.
     *
     * @param string $header
     *
     * @return \Katapoka\Ahgora\IHttpClient the instance of the class for method chaining
     */
    public function unsetHeader($header);

    /**
     * Retrieves the value of a given header name.
     *
     * @param string $header
     *
     * @return string|null
     */
    public function getHeader($header);

    /**
     * Set a timeout to the connection.
     *
     * @param int $timeout
     *
     * @return \Katapoka\Ahgora\IHttpClient
     */
    public function setTimeout($timeout);

    /**
     * Set if the request will response a json instead of form data.
     *
     * @param bool $isJson
     *
     * @return \Katapoka\Ahgora\IHttpClient
     */
    public function setIsJson($isJson = true);
}
