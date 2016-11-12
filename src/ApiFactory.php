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
     * @return \Katapoka\Ahgora\AbstractApi
     */
    public static function create(IHttpClient $httpCLient, $type)
    {
        $className = sprintf('%s\\%sApi', __NAMESPACE__, $type);
        if (class_exists($className)) {
            return new $className($httpCLient);
        }

        throw new InvalidArgumentException("Api type `{$type}` not found.");
    }
}
