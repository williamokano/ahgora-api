<?php
namespace Katapoka\Ahgora;

use InvalidArgumentException;
use Katapoka\Ahgora\Contracts\IHttpClient;

class ApiFactory
{
    /**
     * Tries to instantiate an API class compatible.
     *
     * @param IHttpClient $httpCLient
     * @param string      $type
     *
     * @throws InvalidArgumentException
     *
     * @return \Katapoka\Ahgora\Contracts\IAhgoraApi
     */
    public function create(IHttpClient $httpCLient, $type)
    {
        $className = sprintf('%sApi', $type);
        if (class_exists($className)) {
            return new $className($httpCLient);
        }

        throw new InvalidArgumentException("Api type `{$type}` not found.");
    }
}