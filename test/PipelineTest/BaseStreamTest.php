<?php

namespace PipelineTest;

use Iterator;
use ArrayObject;
use ArrayIterator;
use Pipeline\Stream;
use Pipeline\Collector;

abstract class BaseStreamTest extends TestCase
{
    protected abstract function createStream(Iterator $source) : Stream;

    public function testReducePipeline()
    {
        $values   = [2, 4, 8, 18, 32];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream->reduce(function(int $item, $state) {
            return $state + $item;
        }, 0);

        $this->assertEquals(64, $result);
    }

    public function testToArray()
    {
        $values   = range(1, 10);
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream->filter(function(int $e) {
            return $e % 2 == 0;
        })
        ->toArray();

        $this->assertEquals([2, 4, 6, 8, 10], $result);
    }

    public function testFlatMap()
    {
        $values   = [[1,2,3], [4,5,6], [7,8,9]];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result = $stream->flatMap(function(array $e) {
            return $e;
        })->toArray();

        $this->assertEquals([1,2,3,4,5,6,7,8,9], $result);
    }

    public function testSorted()
    {
        $values   = [5, 4, 3, 2, 1];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream
            ->sorted()
            ->toArray();

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testSortedWithFunction()
    {
        $values   = [2, 3, 1];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream->sorted(function (string $a, string $b) {
            if ($a === $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        })->toArray();

        $this->assertEquals([1, 2, 3], $result);
    }

    public function testLimit()
    {
        $values   = range(1, 10);
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream
            ->limit(5)
            ->toArray();

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testSkip()
    {
        $values   = range(1, 10);
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream
            ->skip(5)
            ->toArray();

        $this->assertEquals([6, 7, 8, 9, 10], $result);
    }

    public function testSlice()
    {
        $values   = range(1, 10);
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream
            ->skip(4)
            ->limit(4)
            ->toArray();

        $this->assertEquals([5, 6, 7, 8], $result);
    }

    public function testMin()
    {
        $values   = [1, 5, 5, 3, 10];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream->min();

        $this->assertEquals(1, $result);
    }

    public function testMax()
    {
        $values   = [1, 7, 5, 5, 2];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream->max();

        $this->assertEquals(7, $result);
    }

    public function testCount()
    {
        $values   = range(1, 10);
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream->count();

        $this->assertEquals(10, $result);
    }

    public function testFindFirst()
    {
        $values   = [5, 4, 3, 2, 1];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream->findFirst();

        $this->assertEquals(5, $result);
    }

    public function testFindFirstMatch()
    {
        $values   = [5, 4, 3, 2, 1];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream->findFirst(function(int $e) {
            return ($e < 5) && ($e % 2 != 0);
        });

        $this->assertEquals(3, $result);
    }

    public function testAnyMatch()
    {
        $values   = [5, 4, 3, 2, 1];
        $iterator = new ArrayIterator($values);
        $stream1  = $this->createStream($iterator);
        $stream2  = $this->createStream($iterator);

        $this->assertTrue($stream1->anyMatch(function(int $e) {
            return $e === 3;
        }));

        $this->assertFalse($stream2->anyMatch(function(int $e) {
            return $e === 10;
        }));
    }

    public function testAllMatch()
    {
        $values   = [5, 4, 3, 2, 1];
        $iterator = new ArrayIterator($values);
        $stream1  = $this->createStream($iterator);
        $stream2  = $this->createStream($iterator);

        $this->assertTrue($stream1->allMatch(function(int $e) {
            return $e < 10;
        }));

        $this->assertFalse($stream2->allMatch(function(int $e) {
            return $e > 2;
        }));
    }

    public function testNoneMatch()
    {
        $values   = [5, 4, 3, 2, 1];
        $iterator = new ArrayIterator($values);
        $stream1  = $this->createStream($iterator);
        $stream2  = $this->createStream($iterator);

        $this->assertTrue($stream1->noneMatch(function(int $e) {
            return $e > 10;
        }));

        $this->assertFalse($stream2->noneMatch(function(int $e) {
            return $e < 5;
        }));
    }

    public function testCollect()
    {
        $values    = [1, 2, 3];
        $iterator  = new ArrayIterator($values);
        $stream    = $this->createStream($iterator);
        $collector = $this->createMock(Collector::CLASS);

        $collector
            ->expects($this->once())
            ->method('begin')
            ->willReturn(0);

        $collector
            ->expects($this->once())
            ->method('finish')
            ->willReturn(1 + 2 + 3);

        $collector
            ->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                [$this->equalTo(0), $this->equalTo(1)],
                [$this->equalTo(0), $this->equalTo(2)],
                [$this->equalTo(0), $this->equalTo(3)]
            );

        $this->assertEquals(1 + 2 + 3, $stream->collect($collector));
    }

    public function testDistinct()
    {
        $values   = [1, 2, 2, 3, 4, 5, 5, 1];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream->distinct()->toArray();

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testPeek()
    {
        $peeks    = new ArrayObject();
        $values   = [1, 2, 3, 4, 5];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);

        $result = $stream
            ->peek(function(int $e) use ($peeks) {
                $peeks[] = $e;
            })
            ->filter(function(int $e) {
                return ($e % 2 != 0);
            })
            ->toArray();

        $this->assertCount(5, $peeks);
        $this->assertCount(3, $result);
        $this->assertEquals([1, 3, 5], $result);
        $this->assertEquals([1, 2, 3, 4, 5], $peeks->getArrayCopy());
    }

    public function testForEach()
    {
        $values   = array_reverse(range(0, 10));
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = new ArrayObject();

        $stream
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
