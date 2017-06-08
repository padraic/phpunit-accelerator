<?php

namespace MyBuilder\PhpunitAccelerator;

use Exception;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestListener as BaseTestListener;
use ReflectionObject;
use ReflectionProperty;

class TestListener implements BaseTestListener
{
    /*private*/ const PHPUNIT_PROPERTY_PREFIX = 'PHPUnit';

    /**
     * @var bool
     */
    private $filterRegisterShutdownFunction;

    /**
     * @param bool $filterRegisterShutdownFunction
     */
    public function __construct(bool $filterRegisterShutdownFunction = false)
    {
        $this->filterRegisterShutdownFunction = $filterRegisterShutdownFunction;
    }

    /**
     * {@inheritdoc}
     */
    public function endTest(Test $test, $time)
    {
        $this->safelyFreeProperties($test);
    }

    /**
     * @param Test $test
     */
    private function safelyFreeProperties(Test $test): void
    {
        foreach ($this->getProperties($test) as $property) {
            if ($this->isSafeToFreeProperty($property)) {
                $this->freeProperty($test, $property);
            }
        }
    }

    /**
     * @param Test $test
     *
     * @return ReflectionProperty[]
     */
    private function getProperties(Test $test): array
    {
        $reflection = new ReflectionObject($test);

        if ($this->filterRegisterShutdownFunction === true && $this->registersShutdownFunction($reflection)) {
            return array();
        }

        return $reflection->getProperties();
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return bool
     */
    private function isSafeToFreeProperty(ReflectionProperty $property): bool
    {
        return !$property->isStatic() && $this->isNotPhpUnitProperty($property);
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return bool
     */
    private function isNotPhpUnitProperty(ReflectionProperty $property): bool
    {
        $fqdn = $property->getDeclaringClass()->getName();
        $fqdnParts = explode('\\', $fqdn);

        return 0 !== stripos($fqdnParts[count($fqdnParts) - 1], self::PHPUNIT_PROPERTY_PREFIX);
    }

    /**
     * @param Test $test
     * @param ReflectionProperty $property
     */
    private function freeProperty(Test $test, ReflectionProperty $property): void
    {
        $property->setAccessible(true);
        $property->setValue($test, null);
    }

    /**
     * @param ReflectionObject $object
     *
     * @return bool
     */
    private function registersShutdownFunction(ReflectionObject $object): bool
    {
        $fp = fopen($object->getFilename(), 'rb');
        while (!feof($fp)) {
            if (false !== stripos(fread($fp, 4096), 'register_shutdown_function(')) {
                return true;
            }
        }
        fclose($fp);

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function startTestSuite(TestSuite $suite)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addError(Test $test, Exception $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addWarning(Test $test, Warning $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addIncompleteTest(Test $test, Exception $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addSkippedTest(Test $test, Exception $e, $time)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function endTestSuite(TestSuite $suite)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function startTest(Test $test)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addRiskyTest(Test $test, Exception $e, $time)
    {
    }
}
