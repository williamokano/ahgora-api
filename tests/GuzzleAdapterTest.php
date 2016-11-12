<?php

namespace Katapoka\Tests\Ahgora;

use Katapoka\Ahgora\Adapters\GuzzleAdapter;
use Katapoka\Ahgora\Contracts\IHttpClient;
use Katapoka\Ahgora\Contracts\IHttpResponse;
use Exception;
use InvalidArgumentException;
use Mockery;
use PHPUnit_Framework_TestCase;

/**
 * Class GuzzleAdapterTest
 */
class GuzzleAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        $guzzleMock = Mockery::mock('\GuzzleHttp\Client', []);

        $guzzleAdapter = new GuzzleAdapter($guzzleMock);
        $this->assertInstanceOf(GuzzleAdapter::class, $guzzleAdapter);
    }

    public function testRequest()
    {
        $guzzleMock = Mockery::mock('\GuzzleHttp\Client', [
            'request' => Mockery::mock('\Psr\Http\Message\ResponseInterface', [
                'getStatusCode' => 200,
                'getBody'       => 'asdadasd',
                'getHeaders'    => [],
            ]),
        ]);

        $guzzleAdapter = new GuzzleAdapter($guzzleMock);
        $this->assertInstanceOf(GuzzleAdapter::class, $guzzleAdapter);

        $response = $guzzleAdapter->request('POST', 'http://blabla');
        $this->assertInstanceOf(IHttpResponse::class, $response);
        $this->assertObjectHasAttribute('httpStatus', $response);
        $this->assertObjectHasAttribute('body', $response);
        $this->assertEquals(200, $response->getHttpStatus());
        $this->assertTrue(is_string($response->getBody()));
    }

    public function testGet()
    {
        $guzzleMock = Mockery::mock('\GuzzleHttp\Client', [
            'request' => Mockery::mock('\Psr\Http\Message\ResponseInterface', [
                'getStatusCode' => 200,
                'getBody'       => 'asdadasd',
                'getHeaders'    => [],
            ]),
        ]);

        $guzzleAdapter = new GuzzleAdapter($guzzleMock);
        $this->assertInstanceOf(GuzzleAdapter::class, $guzzleAdapter);

        $response = $guzzleAdapter->get('http://blabla');
        $this->assertInstanceOf(IHttpResponse::class, $response);
        $this->assertObjectHasAttribute('httpStatus', $response);
        $this->assertObjectHasAttribute('body', $response);
        $this->assertEquals(200, $response->getHttpStatus());
        $this->assertTrue(is_string($response->getBody()));
    }

    public function testPost()
    {
        $guzzleMock = Mockery::mock('\GuzzleHttp\Client', [
            'request' => Mockery::mock('\Psr\Http\Message\ResponseInterface', [
                'getStatusCode' => 200,
                'getBody'       => 'asdadasd',
                'getHeaders'    => [],
            ]),
        ]);

        $guzzleAdapter = new GuzzleAdapter($guzzleMock);
        $this->assertInstanceOf(GuzzleAdapter::class, $guzzleAdapter);

        $response = $guzzleAdapter->post('http://blabla');
        $this->assertInstanceOf(IHttpResponse::class, $response);
        $this->assertObjectHasAttribute('httpStatus', $response);
        $this->assertObjectHasAttribute('body', $response);
        $this->assertEquals(200, $response->getHttpStatus());
        $this->assertTrue(is_string($response->getBody()));
    }

    public function testSetHeader()
    {
        $guzzleMock = Mockery::mock('\GuzzleHttp\Client', []);

        $guzzleAdapter = new GuzzleAdapter($guzzleMock);
        $this->assertInstanceOf(GuzzleAdapter::class, $guzzleAdapter);

        // Happy path test
        $resp = $guzzleAdapter->setHeader('doge', 'wow, such header');
        $headers = $guzzleAdapter->getHeaders();

        $this->assertTrue(is_array($headers));
        $this->assertEquals(1, count($headers));
        $this->assertArrayHasKey('doge', $headers);
        $this->assertInstanceOf(IHttpClient::class, $resp);

        $resp = $guzzleAdapter->setHeader('okano', 'Meet the creator');
        $headers = $guzzleAdapter->getHeaders();

        $this->assertTrue(is_array($headers));
        $this->assertEquals(2, count($headers));
        $this->assertArrayHasKey('doge', $headers);
        $this->assertArrayHasKey('okano', $headers);
        $this->assertInstanceOf(IHttpClient::class, $resp);

        // Wrong type header
        try {
            $guzzleAdapter->setHeader([], '123123');
            $this->fail('Cannot add an array as header name');
        } catch (Exception $ex) {
            $this->assertInstanceOf(InvalidArgumentException::class, $ex);
        }

        // Wrong type value
        try {
            $guzzleAdapter->setHeader('OKANO', []);
            $this->fail('Cannot add an array as header value');
        } catch (Exception $ex) {
            $this->assertInstanceOf(InvalidArgumentException::class, $ex);
        }
    }

    public function testUnsetHeader()
    {
        $guzzleMock = Mockery::mock('\GuzzleHttp\Client', []);

        $guzzleAdapter = new GuzzleAdapter($guzzleMock);
        $this->assertInstanceOf(GuzzleAdapter::class, $guzzleAdapter);

        // Happy path test
        $resp = $guzzleAdapter->setHeader('doge', 'wow, such header');
        $headers = $guzzleAdapter->getHeaders();

        $this->assertTrue(is_array($headers));
        $this->assertEquals(1, count($headers));
        $this->assertArrayHasKey('doge', $headers);
        $this->assertInstanceOf(IHttpClient::class, $resp);

        // This header doesn't exists so shouldn't remove any header. The count should be 1
        $resp = $guzzleAdapter->unsetHeader('okano');
        $headers = $guzzleAdapter->getHeaders();
        $this->assertEquals(1, count($headers));
        $this->assertArrayHasKey('doge', $headers);
        $this->assertInstanceOf(IHttpClient::class, $resp);

        // This time the header does exists, so the array should be empty and should not exists key doge
        $resp = $guzzleAdapter->unsetHeader('doge');
        $headers = $guzzleAdapter->getHeaders();
        $this->assertEquals(0, count($headers));
        $this->assertArrayNotHasKey('doge', $headers);
        $this->assertInstanceOf(IHttpClient::class, $resp);
    }

    public function testGetHeader()
    {
        $guzzleMock = Mockery::mock('\GuzzleHttp\Client', []);

        $guzzleAdapter = new GuzzleAdapter($guzzleMock);
        $this->assertInstanceOf(GuzzleAdapter::class, $guzzleAdapter);

        $headers = [
            'fakeheader' => 'I am so fake, but I am fabulous!',
            'doge'       => 'I am so fake, but I am fabulous!',
        ];

        foreach ($headers as $header => $value) {
            $guzzleAdapter->setHeader($header, $value);
        }

        foreach ($headers as $header => $value) {
            $gottenHeaderValue = $guzzleAdapter->getHeader($header);
            $this->assertEquals($value, $gottenHeaderValue);
        }

        $anotherHeaderValue = $guzzleAdapter->getHeader('NIELSENLIXO');
        $this->assertNull($anotherHeaderValue);
    }

    public function testGetHeaders()
    {
        $guzzleMock = Mockery::mock('\GuzzleHttp\Client', []);

        $guzzleAdapter = new GuzzleAdapter($guzzleMock);
        $this->assertInstanceOf(GuzzleAdapter::class, $guzzleAdapter);

        $guzzleHeaders = $guzzleAdapter->getHeaders();
        $this->assertTrue(is_array($guzzleHeaders));
        $this->assertEmpty($guzzleHeaders);

        $headers = [
            'fakeheader' => 'I am so fake, but I am fabulous!',
            'doge'       => 'I am so fake, but I am fabulous!',
        ];

        foreach ($headers as $header => $value) {
            $guzzleAdapter->setHeader($header, $value);
        }

        $guzzleHeaders = $guzzleAdapter->getHeaders();
        $this->assertTrue(is_array($guzzleHeaders));
        $this->assertEquals(2, count($guzzleHeaders));
    }

    public function testSetTimeout()
    {
        $guzzleMock = Mockery::mock('\GuzzleHttp\Client', []);

        $guzzleAdapter = new GuzzleAdapter($guzzleMock);
        $this->assertInstanceOf(GuzzleAdapter::class, $guzzleAdapter);

        $guzzleAdapter->setTimeout(10);
        $this->assertAttributeEquals(10, 'timeout', $guzzleAdapter);

        $guzzleAdapter->setTimeout(20);
        $this->assertAttributeEquals(20, 'timeout', $guzzleAdapter);
    }

    public function testSetIsJson()
    {
        $guzzleMock = Mockery::mock('\GuzzleHttp\Client', []);

        $guzzleAdapter = new GuzzleAdapter($guzzleMock);
        $this->assertInstanceOf(GuzzleAdapter::class, $guzzleAdapter);

        $this->assertAttributeEquals(false, 'isJson', $guzzleAdapter);

        $guzzleAdapter->setIsJson(true);
        $contentTypeHeader = $guzzleAdapter->getHeader('content-type');
        $this->assertAttributeEquals(true, 'isJson', $guzzleAdapter);
        $this->assertEquals('application/json', $contentTypeHeader);

        $guzzleAdapter->setIsJson(false);
        $contentTypeHeader = $guzzleAdapter->getHeader('content-type');
        $this->assertAttributeEquals(false, 'isJson', $guzzleAdapter);
        $this->assertNull($contentTypeHeader);

        try {
            $guzzleAdapter->setIsJson('UMASTRINGQUALQUER');
            $this->fail("isJson should've failed since I used a string as parameter");
        } catch (Exception $ex) {
            $this->assertInstanceOf(InvalidArgumentException::class, $ex);
        }
    }
}
