<?php

namespace MyBuilder\PhpunitAccelerator;

use PHPUnit\Framework\Warning;

class TestListener implements \PHPUnit\Framework\TestListener
{
    const PHPUNIT_PROPERTY_PREFIX = 'PHPUnit_';

    private $filterRegisterShutdownFunction;

    public function __construct($filterRegisterShutdownFunction = false)
    {
        $this->filterRegisterShutdownFunction = $filterRegisterShutdownFunction;
    }

    public function endTest(\PHPUnit\Framework\Test $test, $time)
    {
        $this->safelyFreeProperties($test);
    }

    private function safelyFreeProperties($test)
    {
        foreach ($this->getProperties($test) as $property) {
            if ($this->isSafeToFreeProperty($property)) {
                $this->freeProperty($test, $property);
            }
        }
    }

    private function getProperties($test)
    {
        $reflection = new \ReflectionObject($test);

        if ($this->filterRegisterShutdownFunction === true && $this->registersShutdownFunction($reflection)) {
            return array();
        }

        return $reflection->getProperties();
    }

    private function isSafeToFreeProperty($property)
    {
        return !$property->isStatic() && $this->isNotPhpUnitProperty($property);
    }

    private function isNotPhpUnitProperty($property)
    {
        return 0 !== strpos($property->getDeclaringClass()->getName(), self::PHPUNIT_PROPERTY_PREFIX);
    }

    private function freeProperty($test, $property)
    {
        $property->setAccessible(true);
        $property->setValue($test, null);
    }

    private function registersShutdownFunction(\ReflectionObject $object)
    {
        $fp = fopen($object->getFilename(), 'rb');
        while (!feof($fp)) {
            if (false !== stripos(fread($fp, 4096), 'register_shutdown_function(')) {
                return true;
            }
        }
        fclose($fp);
    }

    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite) {}

    public function addError(\PHPUnit\Framework\Test $test, \Exception $e, $time) {}

    public function addWarning(\PHPUnit\Framework\Test $test, Warning $e, $time) {}

    public function addFailure(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, $time) {}

    public function addIncompleteTest(\PHPUnit\Framework\Test $test, \Exception $e, $time) {}

    public function addSkippedTest(\PHPUnit\Framework\Test $test, \Exception $e, $time) {}

    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite) {}

    public function startTest(\PHPUnit\Framework\Test $test) {}

    public function addRiskyTest(\PHPUnit\Framework\Test $test, \Exception $e, $time) {}
}
