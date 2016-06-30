<?php

namespace PipelineTest;

use ArrayObject;
use ArrayIterator;
use Pipeline\ReferencePipeline;

class ReferencePipelineTest extends TestCase
{
    public function testReducePipeline()
    {
        $iterator = new ArrayIterator([2, 4, 8, 18, 32]);
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline
            ->reduce(function(int $item, $state) {
                return $state + $item;
            }, 0);

        $this->assertEquals(64, $result);
    }

    public function testToArray()
    {
        $iterator = new ArrayIterator(range(1, 10));
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline
            ->filter(function(int $e) {
                return $e % 2 == 0;
            })
            ->toArray();

        $this->assertEquals([2, 4, 6, 8, 10], $result);
    }

    public function testFlatMap()
    {
        $iterator = new ArrayIterator([[1,2,3], [4,5,6], [7,8,9]]);
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline
            ->flatMap(function(array $e) {
                return $e;
            })
            ->toArray();

        $this->assertEquals([1,2,3,4,5,6,7,8,9], $result);
    }

    public function testForEach()
    {
        $iterator = new ArrayIterator(range(0, 10));
        $pipeline = new ReferencePipeline($iterator);
        $result   = new ArrayObject();

        $pipeline
            ->filter(function(int $e) {
                return $e % 2 == 0;
            })
            ->map(function(int $e) {
                return $e + $e;
            })
            ->filter(function(int $e) {
                return ($e > 0 && $e < 10);
            })
            ->forEach(function(int $e) use ($result) {
                $result[] = $e;
            });

        $this->assertCount(2, $result);
        $this->assertEquals(4, $result[0]);
        $this->assertEquals(8, $result[1]);
    }
}
