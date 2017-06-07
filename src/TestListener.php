<?php

namespace MyBuilder\PhpunitAccelerator;

use Exception;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestListener as BaseTestListener;
use ReflectionObject;

class TestListener implements BaseTestListener
{
    const PHPUNIT_PROPERTY_PREFIX = 'PHPUnit';

    private $filterRegisterShutdownFunction;

    public function __construct($filterRegisterShutdownFunction = false)
    {
        $this->filterRegisterShutdownFunction = $filterRegisterShutdownFunction;
    }

    public function endTest(Test $test, $time)
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
        $reflection = new ReflectionObject($test);

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
        $fqdn = $property->getDeclaringClass()->getName();
        $fqdnParts = explode('\\', $fqdn);

        return 0 !== stripos($fqdnParts[count($fqdnParts) - 1], self::PHPUNIT_PROPERTY_PREFIX);
    }

    private function freeProperty($test, $property)
    {
        $property->setAccessible(true);
        $property->setValue($test, null);
    }

    private function registersShutdownFunction(ReflectionObject $object)
    {
        $fp = fopen($object->getFilename(), 'rb');
        while (!feof($fp)) {
            if (false !== stripos(fread($fp, 4096), 'register_shutdown_function(')) {
                return true;
            }
        }
        fclose($fp);
    }

    public function startTestSuite(TestSuite $suite)
    {
    }

    public function addError(Test $test, Exception $e, $time)
    {
    }

    public function addWarning(Test $test, Warning $e, $time)
    {
    }

    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
    }

    public function addIncompleteTest(Test $test, Exception $e, $time)
    {
    }

    public function addSkippedTest(Test $test, Exception $e, $time)
    {
    }

    public function endTestSuite(TestSuite $suite)
    {
    }

    public function startTest(Test $test)
    {
    }

    public function addRiskyTest(Test $test, Exception $e, $time)
    {
    }
}
