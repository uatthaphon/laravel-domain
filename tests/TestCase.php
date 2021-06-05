<?php

namespace Tests;

use Uatthaphon\LaravelDomain\LaravelDomainServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelDomainServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }

    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function getStrictAccessProperty(&$object, $propertyName)
    {
        $reflector = new \ReflectionClass(get_class($object));
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
