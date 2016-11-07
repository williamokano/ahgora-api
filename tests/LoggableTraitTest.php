<?php

namespace Katapoka\Tests\Ahgora;

use Katapoka\Ahgora\Loggable;
use Mockery;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use ReflectionClass;

class LoggableTraitTest extends PHPUnit_Framework_TestCase
{
    /** @var Loggable */
    private $traitObject;

    public function setUp()
    {
        $this->traitObject = $this->createObjectForTrait();
    }

    private function createObjectForTrait()
    {
        $traitName = Loggable::class;

        return $this->getObjectForTrait($traitName);
    }

    public function testSetLogger()
    {
        $logger = Mockery::mock(LoggerInterface::class, [

        ]);
        $this->assertObjectHasAttribute('logger', $this->traitObject);
        $this->assertAttributeEquals(null, 'logger', $this->traitObject);

        $this->traitObject->setLogger($logger);
        $this->assertAttributeInstanceOf(LoggerInterface::class, 'logger', $this->traitObject);
    }

    public function testLog()
    {
        $logger = Mockery::mock(LoggerInterface::class, [
            'log' => null,
        ]);
        $this->traitObject->setLogger($logger);

        $res = $this->invokeMethod($this->traitObject, 'log', [LogLevel::DEBUG, 'Teste log debug']);
        $this->assertInstanceOf(get_class($this->traitObject), $res);

        $res = $this->invokeMethod($this->traitObject, 'log', [LogLevel::ALERT, 'Teste log alert']);
        $this->assertInstanceOf(get_class($this->traitObject), $res);

        $res = $this->invokeMethod($this->traitObject, 'log', [LogLevel::CRITICAL, 'Teste log critical']);
        $this->assertInstanceOf(get_class($this->traitObject), $res);

        $res = $this->invokeMethod($this->traitObject, 'log', [LogLevel::EMERGENCY, 'Teste log emergency']);
        $this->assertInstanceOf(get_class($this->traitObject), $res);

        $res = $this->invokeMethod($this->traitObject, 'log', [LogLevel::ERROR, 'Teste log error']);
        $this->assertInstanceOf(get_class($this->traitObject), $res);

        $res = $this->invokeMethod($this->traitObject, 'log', [LogLevel::INFO, 'Teste log info']);
        $this->assertInstanceOf(get_class($this->traitObject), $res);

        $res = $this->invokeMethod($this->traitObject, 'log', [LogLevel::NOTICE, 'Teste log notice']);
        $this->assertInstanceOf(get_class($this->traitObject), $res);

        $res = $this->invokeMethod($this->traitObject, 'log', [LogLevel::WARNING, 'Teste log warning']);
        $this->assertInstanceOf(get_class($this->traitObject), $res);

        $this->assertTrue(true);
    }

    public function testLogLevels()
    {
        $logger = Mockery::mock(LoggerInterface::class, [
            'log' => null,
        ]);
        $this->traitObject->setLogger($logger);

        $data = [
            ['level' => LogLevel::DEBUG, 'message' => 'Message ' . LogLevel::DEBUG],
            ['level' => LogLevel::ALERT, 'message' => 'Message ' . LogLevel::ALERT],
            ['level' => LogLevel::CRITICAL, 'message' => 'Message ' . LogLevel::CRITICAL],
            ['level' => LogLevel::EMERGENCY, 'message' => 'Message ' . LogLevel::EMERGENCY],
            ['level' => LogLevel::ERROR, 'message' => 'Message ' . LogLevel::ERROR],
            ['level' => LogLevel::INFO, 'message' => 'Message ' . LogLevel::INFO],
            ['level' => LogLevel::NOTICE, 'message' => 'Message ' . LogLevel::NOTICE],
            ['level' => LogLevel::WARNING, 'message' => 'Message ' . LogLevel::WARNING],
        ];

        foreach ($data as $log) {
            $res = $this->invokeMethod($this->traitObject, $log['level'], [$log['message']]);
            $this->assertInstanceOf(get_class($this->traitObject), $res);
        }
    }

    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
