<?php

namespace PipelineTest\Collector;

use PipelineTest\TestCase;

use ArrayObject;
use Pipeline\Collector\AverageCollector;

class AverageCollectorTest extends TestCase
{
    public function testCollector()
    {
        $collector = new AverageCollector();
        $state     = $collector->begin();

        $this->assertInternalType('object', $state);
        $this->assertObjectHasAttribute('sum', $state);
        $this->assertObjectHasAttribute('count', $state);
        $this->assertEquals(0, $state->count);
        $this->assertEquals(0, $state->sum);

        $collector->accept($state, 4);
        $collector->accept($state, 6);
        $collector->accept($state, 2);

        $this->assertEquals(12, $state->sum);
        $this->assertEquals(3, $state->count);
        $this->assertEquals(4, $collector->finish($state));
    }

    public function testCollectorEmpty()
    {
        $collector = new AverageCollector();
        $state     = $collector->begin();

        $this->assertInternalType('object', $state);
        $this->assertObjectHasAttribute('sum', $state);
        $this->assertObjectHasAttribute('count', $state);

        $this->assertEquals(0, $state->sum);
        $this->assertEquals(0, $state->count);
        $this->assertEquals(0, $collector->finish($state));
    }
}
