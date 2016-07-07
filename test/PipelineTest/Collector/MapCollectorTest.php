<?php

namespace PipelineTest\Collector;

use PipelineTest\TestCase;

use ArrayObject;
use Pipeline\Collector\MapCollector;
use Pipeline\Collector\ArrayCollector;

class MapCollectorTest extends TestCase
{
    public function testCollector()
    {
        $downstream = new ArrayCollector();
        $collector  = new MapCollector(function (string $item) {
            return strtoupper($item);
        }, $downstream);

        $state = $collector->begin();

        $this->assertInstanceOf('ArrayObject', $state);
        $this->assertCount(0, $state);

        $collector->accept($state, 'one');
        $collector->accept($state, 'two');
        $collector->accept($state, 'three');

        $this->assertEquals(['ONE', 'TWO', 'THREE'], $state->getArrayCopy());
        $this->assertEquals(['ONE', 'TWO', 'THREE'], $collector->finish($state));
    }
}
