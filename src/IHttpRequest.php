<?php
namespace Katapoka\Ahgora;

/**
 * Interface responsible for defining the default contract (instead of psr-7)
 * of how to make http requests.
 *
 * @package Katapoka\Ahgora
 */
interface IHttpRequest
{
    public function get($url, $data);
    public function post($url, $data);
    public function setHeader($header, $value);
    public function getHeader($header);
    public function setTimeout($ttl);
}