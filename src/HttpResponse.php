<?php

namespace Katapoka\Ahgora;

use Katapoka\Ahgora\Contracts\IHttpResponse;

/**
 * Class for the Http responses
 */
class HttpResponse implements IHttpResponse
{
    /** @var int The http response status code */
    private $httpStatus;

    /** @var string The response body */
    private $body;

    /** @var array The response headers */
    private $headers = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Get the HttpResponse status.
     *
     * @return mixed
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /**
     * Get the request body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the request body as json, if is json compatible.
     *
     * @return string
     */
    public function json()
    {
        $json = json_decode($this->body);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Failed to decode json:' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * The the response headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
