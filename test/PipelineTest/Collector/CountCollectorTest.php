<?php

namespace PipelineTest\Collector;

use PipelineTest\TestCase;

use ArrayObject;
use Pipeline\Collector\CountCollector;

class CountCollectorTest extends TestCase
{
    public function testCollector()
    {
        $collector = new CountCollector();
        $state     = $collector->begin();

        $this->assertInternalType('object', $state);
        $this->assertObjectHasAttribute('count', $state);
        $this->assertEquals(0, $state->count);

        $collector->accept($state, 2);
        $collector->accept($state, 4);
        $collector->accept($state, 5);

        $this->assertEquals(3, $state->count);
        $this->assertEquals(3, $collector->finish($state));
    }
}
