<?php

namespace PipelineTest;

use ArrayObject;
use ArrayIterator;
use Pipeline\Collector;
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

    public function testFindFirst()
    {
        $iterator = new ArrayIterator([5, 4, 3, 2, 1]);
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline->findFirst();

        $this->assertEquals(5, $result);
    }

    public function testFindFirstMatch()
    {
        $iterator = new ArrayIterator([5, 4, 3, 2, 1]);
        $pipeline = new ReferencePipeline($iterator);
        $result   = $pipeline->findFirst(function(int $e) {
            return ($e < 5) && ($e % 2 != 0);
        });

        $this->assertEquals(3, $result);
    }

    public function testAnyMatch()
    {
        $iterator  = new ArrayIterator([5, 4, 3, 2, 1]);
        $pipeline1 = new ReferencePipeline($iterator);
        $pipeline2 = new ReferencePipeline($iterator);

        $this->assertTrue($pipeline1->anyMatch(function(int $e) {
            return $e === 3;
        }));

        $this->assertFalse($pipeline2->anyMatch(function(int $e) {
            return $e === 10;
        }));
    }

    public function testAllMatch()
    {
        $iterator  = new ArrayIterator([5, 4, 3, 2, 1]);
        $pipeline1 = new ReferencePipeline($iterator);
        $pipeline2 = new ReferencePipeline($iterator);

        $this->assertTrue($pipeline1->allMatch(function(int $e) {
            return $e < 10;
        }));

        $this->assertFalse($pipeline2->allMatch(function(int $e) {
            return $e > 2;
        }));
    }

    public function testNoneMatch()
    {
        $iterator  = new ArrayIterator([5, 4, 3, 2, 1]);
        $pipeline1 = new ReferencePipeline($iterator);
        $pipeline2 = new ReferencePipeline($iterator);

        $this->assertTrue($pipeline1->noneMatch(function(int $e) {
            return $e > 10;
        }));

        $this->assertFalse($pipeline2->noneMatch(function(int $e) {
            return $e < 5;
        }));
    }

    public function testCollect()
    {
        $values    = ['one', 'two', 'three'];
        $iterator  = new ArrayIterator($values);
        $pipeline  = new ReferencePipeline($iterator);
        $collector = $this->createMock(Collector::CLASS);

        $collector
            ->expects($this->once())
            ->method('get')
            ->willReturn('foo-bar');

        $collector
            ->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                $this->equalTo('one'),
                $this->equalTo('two'),
                $this->equalTo('three')
            );

        $this->assertEquals('foo-bar', $pipeline->collect($collector));
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

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 passed to Pipeline\BasePipeline::__construct() must be an instance of "Iterator" or "Pipeline\BasePipeline", "array" given.
     */
    public function testConstructArrayException()
    {
        new ReferencePipeline([]);
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 passed to Pipeline\BasePipeline::__construct() must be an instance of "Iterator" or "Pipeline\BasePipeline", "stdClass" given.
     */
    public function testConstructStdClassException()
    {
        new ReferencePipeline(new \stdClass);
    }
}
