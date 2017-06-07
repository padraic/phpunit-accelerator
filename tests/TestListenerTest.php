<?php

namespace MyBuilder\PhpunitAccelerator;

use MyBuilder\PhpunitAccelerator\TestListener;
use PHPUnit\Framework\TestCase;

class TestListenerTest extends TestCase
{
    private $listener;
    private $dummyTest;

    private $listenerFiltersShutdownFunction;
    private $dummyTestRegistersShutdownFunction;

    protected function setUp()
    {
        $this->listener = new TestListener();
        $this->dummyTest = new class extends PHPUnitFakeTestCase
        {
            public $property = 1;
        };

        $this->listenerFiltersShutdownFunction = new TestListener(true);
        $this->dummyTestRegistersShutdownFunction = new class extends PHPUnitFakeTestCase
        {
            public $property = 1;

            public function foo()
            {
                register_shutdown_function(function () {
                    return;
                });
            }
        };
    }

    /**
     * @test
     */
    public function shouldFreeProperty()
    {
        $this->endTest();

        $this->assertNull($this->dummyTest->property);
    }

    /**
     * @test
     */
    public function shouldNotFreePhpUnitProperty()
    {
        $this->endTest();

        $this->assertNotNull($this->dummyTest->phpUnitProperty);
    }

    /**
     * @test
     */
    public function shouldNotFreePhpUnitPropertyIfRegistersShutdownFunction()
    {
        $this->listenerFiltersShutdownFunction->endTest(
            $this->dummyTestRegistersShutdownFunction,
            0
        );

        $this->assertNotNull($this->dummyTestRegistersShutdownFunction->property);
    }

    private function endTest()
    {
        $this->listener->endTest($this->dummyTest, 0);
    }
}
