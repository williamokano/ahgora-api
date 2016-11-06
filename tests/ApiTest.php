<?php
namespace Katapoka\Tests\Ahgora;

use Katapoka\Ahgora\Adapters\GuzzleAdapter;
use Katapoka\Ahgora\Api;
use Katapoka\Ahgora\IHttpClient;
use PHPUnit_Framework_TestCase;

/**
 * Class responsible for holding all the Api.php tests.
 */
class ApiTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $guzzleAdapter = new GuzzleAdapter();
        $this->assertInstanceOf(IHttpClient::class, $guzzleAdapter);

        $api = new Api($guzzleAdapter);
        $this->assertInstanceOf(Api::class, $api);
    }
}
