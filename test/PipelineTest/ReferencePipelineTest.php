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

    public function testSorted()
    {
        $iterator = new ArrayIterator([5, 4, 3, 2, 1]);
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline
            ->sorted()
            ->toArray();

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testSortedWithFunction()
    {
        $iterator = new ArrayIterator(['lemons', 'apples', 'grapes']);
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline
            ->sorted(function (string $a, string $b) {
                return strcmp($a, $b);
            })
            ->toArray();

        $this->assertEquals(['apples', 'grapes', 'lemons'], $result);
    }

    public function testLimit()
    {
        $iterator = new ArrayIterator(range(1, 10));
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline
            ->limit(5)
            ->toArray();

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testSkip()
    {
        $iterator = new ArrayIterator(range(1, 10));
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline
            ->skip(5)
            ->toArray();

        $this->assertEquals([6, 7, 8, 9, 10], $result);
    }

    public function testSlice()
    {
        $iterator = new ArrayIterator(range(1, 10));
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline
            ->skip(4)
            ->limit(4)
            ->toArray();

        $this->assertEquals([5, 6, 7, 8], $result);
    }

    public function testMin()
    {
        $iterator = new ArrayIterator([1, 5, 5, 3, 10]);
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline->min();

        $this->assertEquals(1, $result);
    }

    public function testMax()
    {
        $iterator = new ArrayIterator([1, 7, 5, 5, 2]);
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline->max();

        $this->assertEquals(7, $result);
    }

    public function testCount()
    {
        $iterator = new ArrayIterator(range(1, 10));
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline->count();

        $this->assertEquals(10, $result);
    }

    public function testForEach()
    {
        $iterator = new ArrayIterator(array_reverse(range(0, 10)));
        $pipeline = new ReferencePipeline($iterator);
        $result   = new ArrayObject();

        $pipeline
            ->sorted()
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
