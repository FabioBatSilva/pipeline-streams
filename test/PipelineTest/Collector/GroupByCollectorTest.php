<?php

namespace PipelineTest\Collector;

use PipelineTest\TestCase;

use ArrayObject;
use Pipeline\Collector\ArrayCollector;
use Pipeline\Collector\GroupByCollector;

class GroupByCollectorTest extends TestCase
{
    public function testCollector()
    {
        $downstream = new ArrayCollector();
        $collector  = new GroupByCollector(function(array $item) {
            return $item['key'];
        }, $downstream);

        $state = $collector->begin();

        $this->assertInstanceOf('ArrayObject', $state);
        $this->assertCount(0, $state);

        $itemOne11   = ['key' => 'one', 'value' => 11];
        $itemOne12   = ['key' => 'one', 'value' => 12];
        $itemOne13   = ['key' => 'one', 'value' => 13];
        $itemTwo21   = ['key' => 'two', 'value' => 21];
        $itemTwo22   = ['key' => 'two', 'value' => 22];
        $itemThree31 = ['key' => 'three', 'value' => 31];

        $collector->accept($state, $itemOne11);
        $collector->accept($state, $itemOne12);
        $collector->accept($state, $itemOne13);
        $collector->accept($state, $itemTwo21);
        $collector->accept($state, $itemTwo22);
        $collector->accept($state, $itemThree31);

        $this->assertArrayHasKey('one', $state);
        $this->assertArrayHasKey('two', $state);
        $this->assertArrayHasKey('three', $state);
        $this->assertInstanceOf('ArrayObject', $state['one']);
        $this->assertInstanceOf('ArrayObject', $state['two']);
        $this->assertInstanceOf('ArrayObject', $state['three']);

        $result = $collector->finish($state);

        $this->assertArrayHasKey('one', $result);
        $this->assertArrayHasKey('two', $result);
        $this->assertArrayHasKey('three', $result);

        $this->assertCount(3, $result['one']);
        $this->assertCount(2, $result['two']);
        $this->assertCount(1, $result['three']);

        $this->assertEquals($itemOne11, $result['one'][0]);
        $this->assertEquals($itemOne12, $result['one'][1]);
        $this->assertEquals($itemOne13, $result['one'][2]);

        $this->assertEquals($itemTwo21, $result['two'][0]);
        $this->assertEquals($itemTwo22, $result['two'][1]);

        $this->assertEquals($itemThree31, $result['three'][0]);
    }
}
