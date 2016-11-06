<?php

namespace Katapoka\Ahgora;

/**
 * Class responsible for getting the data from the Ahgora system.
 */
class Api
{
    /** @var \Katapoka\Ahgora\IHttpClient */
    private $httpClient;

    public function __construct(IHttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}
