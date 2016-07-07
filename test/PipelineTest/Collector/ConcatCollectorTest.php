<?php

namespace PipelineTest\Collector;

use PipelineTest\TestCase;

use ArrayObject;
use Pipeline\Collector\ConcatCollector;

class ConcatCollectorTest extends TestCase
{
    public function testCollector()
    {
        $collector = new ConcatCollector(', ');
        $state     = $collector->begin();

        $this->assertInstanceOf('ArrayObject', $state);
        $this->assertCount(0, $state);

        $collector->accept($state, 1);
        $collector->accept($state, 2);
        $collector->accept($state, 3);

        $this->assertEquals('1, 2, 3', $collector->finish($state));
    }
}
