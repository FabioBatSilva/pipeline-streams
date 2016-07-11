<?php

namespace PipelineTest\Collector;

use PipelineTest\TestCase;

use ArrayObject;
use Pipeline\Collector\ArrayMapCollector;

class ArrayMapCollectorTest extends TestCase
{
    public function testCollector()
    {
        $keyMapper     = function (int $e) {return $e;};
        $valueMapper   = function (int $e) {return $e;};
        $mergeFunction = function (int $e1, int $e2) {return $e1 + $e2;};
        $collector     = new ArrayMapCollector($keyMapper, $valueMapper, $mergeFunction);
        $state         = $collector->begin();
        $expected      = [
            2  => 4,
            8  => 8,
            16 => 16
        ];

        $this->assertInstanceOf('ArrayObject', $state);
        $this->assertCount(0, $state);

        $collector->accept($state, 2);
        $collector->accept($state, 2);
        $collector->accept($state, 8);
        $collector->accept($state, 16);

        $this->assertEquals($expected, $state->getArrayCopy());
        $this->assertEquals($expected, $collector->finish($state));
    }
}
