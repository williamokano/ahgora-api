<?php

namespace Katapoka\Ahgora;

/**
 * Class for the Http responses
 */
class HttpResponse
{
    /** @var int The http response status code */
    public $httpStatus;

    /** @var string The response body */
    public $body;

    /** @var array The response headers */
    public $headers = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
