<?php

namespace Katapoka\Ahgora;

/**
 * Interface responsible for defining the default contract (instead of psr-7)
 * of how to make http requests.
 */
interface IHttpClient
{
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
    public function request($method, $url, $data = array(), array $config = array());

    /**
     * Make a get request to an URL.
     *
     * @param string $url
     * @param array  $data
     * @param array  $config
     *
     * @return \Katapoka\Ahgora\HttpResponse
     */
    public function get($url, $data = array(), array $config = array());

    /**
     * Make a post request to an URL.
     *
     * @param string $url
     * @param array  $data
     * @param array  $config
     *
     * @return \Katapoka\Ahgora\HttpResponse
     */
    public function post($url, $data = array(), array $config = array());

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
     * Retrieves the value of a given header name.
     *
     * @param string $header
     *
     * @return string
     */
    public function getHeader($header);

    /**
     * Set a timeout to the connection.
     *
     * @param int $ttl
     *
     * @return \Katapoka\Ahgora\IHttpClient
     */
    public function setTimeout($ttl);
}
