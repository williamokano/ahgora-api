<?php
namespace Katapoka\Tests\Ahgora;

use Katapoka\Ahgora\Api;
use PHPUnit_Framework_TestCase;

/**
 * Class responsible for holding all the Api.php tests.
 *
 * @package Katapoka\Tests\Ahgora
 */
class ApiTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $api = new Api();
        $this->assertInstanceOf(Api::class, $api);
    }
}