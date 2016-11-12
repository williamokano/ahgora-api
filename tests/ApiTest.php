<?php
namespace Katapoka\Tests\Ahgora;

use Katapoka\Ahgora\Adapters\GuzzleAdapter;
use Katapoka\Ahgora\HttpApi;
use Katapoka\Ahgora\Contracts\IHttpClient;
use PHPUnit_Framework_TestCase;

/**
 * Class responsible for holding all the HttpApi.php tests.
 */
class ApiTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $guzzleMock = \Mockery::mock('\GuzzleHttp\Client', [

        ]);

        $guzzleAdapter = new GuzzleAdapter($guzzleMock);
        $this->assertInstanceOf(IHttpClient::class, $guzzleAdapter);

        $api = new HttpApi($guzzleAdapter);
        $this->assertInstanceOf(HttpApi::class, $api);
    }
}
