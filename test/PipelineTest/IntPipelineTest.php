<?php

namespace PipelineTest;

use Traversable;
use ArrayObject;
use ArrayIterator;

use Pipeline\Stream;
use Pipeline\Collector;
use Pipeline\IntPipeline;

class IntPipelineTest extends TestCase
{
    public function testFluentInterface()
    {
        $stream = IntPipeline::wrap([]);

        $this->assertInstanceOf('Pipeline\IntPipeline', $stream->sorted());
        $this->assertInstanceOf('Pipeline\IntPipeline', $stream->skip(10));
        $this->assertInstanceOf('Pipeline\IntPipeline', $stream->distinct());
        $this->assertInstanceOf('Pipeline\IntPipeline', $stream->map('floatval'));
        $this->assertInstanceOf('Pipeline\IntPipeline', $stream->peek('var_dump'));
        $this->assertInstanceOf('Pipeline\IntPipeline', $stream->filter('is_float'));
        $this->assertInstanceOf('Pipeline\IntPipeline', $stream->flatMap(function(float $f){
            return [$f];
        }));

        $this->assertInstanceOf('Pipeline\FloatPipeline', $stream->mapToFloat('floatval'));
        $this->assertInstanceOf('Pipeline\FloatPipeline', $stream->flatMapToFloat(function(float $f){
            return [floatval($f)];
        }));
    }

    public function testMap()
    {
        $stream = IntPipeline::of(1, 2, 3);
        $result = $stream->map(function(int $e) : int {
            return $e * $e;
        })->toArray();

        $this->assertSame([1, 4, 9], $result);
    }

    public function testMapToFloat()
    {
        $intStream   = IntPipeline::of(1, 2, 3);
        $floatStream = $intStream->mapToFloat(function(int $e) : float {
            return ($e * $e) / 0.5;
        });

        $this->assertInstanceOf('Pipeline\FloatStream', $floatStream);
        $this->assertSame(28.0, $floatStream->sum());
    }

    public function testReducePipeline()
    {
        $stream = IntPipeline::of(2, 4, 8, 18, 32);
        $result = $stream->reduce(0, function(int $state, int $item) {
            return $state + $item;
        });

        $this->assertSame(64, $result);
    }

    public function testToArray()
    {
        $stream = IntPipeline::wrap(range(1, 10));
        $result = $stream->filter(function(int $e) {
            return $e % 2 == 0;
        })
        ->toArray();

        $this->assertSame([2, 4, 6, 8, 10], $result);
    }

    public function testFlatMap()
    {
        $stream = IntPipeline::of(1, 2, 3);
        $result = $stream->flatMap(function(int $e) : array {
            return [$e * 1, $e * 2, $e * 3];
        })->toArray();

        $this->assertSame([1, 2, 3, 2, 4, 6, 3, 6, 9], $result);
    }

    public function testFlatMapToInt()
    {
        $intStream   = IntPipeline::of(1, 2, 3);
        $floatStream = $intStream->flatMapToFLoat(function(int $e) : array {
            return [floatval($e * 1), floatval($e * 2)];
        });

        $this->assertInstanceOf('Pipeline\FloatStream', $floatStream);
        $this->assertSame(18.0, $floatStream->sum());
    }

    public function testSorted()
    {
        $stream = IntPipeline::of(5, 4, 3, 2, 1);
        $result = $stream
            ->sorted()
            ->toArray();

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testSortedWithFunction()
    {
        $stream = IntPipeline::of(2, 3, 1);
        $result = $stream->sorted(function (int $a, int $b) {
            return $a <=> $b;
        })->toArray();

        $this->assertSame([1, 2, 3], $result);
    }

    public function testLimit()
    {
        $values = range(1, 10);
        $stream = IntPipeline::wrap($values);
        $result = $stream
            ->limit(5)
            ->toArray();

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testSkip()
    {
        $values = range(1, 10);
        $stream = IntPipeline::wrap($values);
        $result = $stream
            ->skip(5)
            ->toArray();

        $this->assertSame([6, 7, 8, 9, 10], $result);
    }

    public function testSlice()
    {
        $values = range(1, 10);
        $stream = IntPipeline::wrap($values);
        $result = $stream
            ->skip(4)
            ->limit(4)
            ->toArray();

        $this->assertSame([5, 6, 7, 8], $result);
    }

    public function testMin()
    {
        $stream = IntPipeline::of(1, 5, 5, 3, 10);
        $result = $stream->min();

        $this->assertSame(1, $result);
    }

    public function testMax()
    {
        $stream = IntPipeline::of(1, 7, 5, 5, 2);
        $result = $stream->max();

        $this->assertSame(7, $result);
    }

    public function testCount()
    {
        $values = range(1, 10);
        $stream = IntPipeline::wrap($values);
        $result = $stream->count();

        $this->assertEquals(10, $result);
    }

    public function testFindFirst()
    {
        $stream = IntPipeline::of(5, 4, 3, 2, 1);
        $result = $stream->findFirst();

        $this->assertEquals(5, $result);
    }

    public function testFindFirstMatch()
    {
        $stream = IntPipeline::of(5, 4, 3, 2, 1);
        $result = $stream->findFirst(function(int $e) {
            return ($e < 5) && ($e % 2 != 0);
        });

        $this->assertEquals(3, $result);
    }

    public function testAnyMatch()
    {
        $values  = [5, 4, 3, 2, 1];
        $stream1 = IntPipeline::wrap($values);
        $stream2 = IntPipeline::wrap($values);

        $this->assertTrue($stream1->anyMatch(function(int $e) {
            return $e === 3;
        }));

        $this->assertFalse($stream2->anyMatch(function(int $e) {
            return $e === 10;
        }));
    }

    public function testAllMatch()
    {
        $values  = [5, 4, 3, 2, 1];
        $stream1 = IntPipeline::wrap($values);
        $stream2 = IntPipeline::wrap($values);

        $this->assertTrue($stream1->allMatch(function(int $e) {
            return $e < 10;
        }));

        $this->assertFalse($stream2->allMatch(function(int $e) {
            return $e > 2;
        }));
    }

    public function testNoneMatch()
    {
        $values  = [5, 4, 3, 2, 1];
        $stream1 = IntPipeline::wrap($values);
        $stream2 = IntPipeline::wrap($values);

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
        $stream    = IntPipeline::of(1, 2, 3);
        $collector = $this->createMock(Collector::CLASS);

        $collector
            ->expects($this->once())
            ->method('begin')
            ->willReturn(0);

        $collector
            ->expects($this->once())
            ->method('finish')
            ->willReturn(6);

        $collector
            ->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                [$this->equalTo(0), $this->equalTo(1)],
                [$this->equalTo(0), $this->equalTo(2)],
                [$this->equalTo(0), $this->equalTo(3)]
            );

        $this->assertSame(6, $stream->collect($collector));
    }

    public function testDistinct()
    {
        $values = [1, 2, 2, 3, 4, 5, 5, 1];
        $stream = IntPipeline::wrap($values);
        $result = $stream->distinct()->toArray();

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testPeek()
    {
        $peeks  = new ArrayObject();
        $values = [1, 2, 3, 4, 5];
        $stream = IntPipeline::wrap($values);

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
        $values = range(1, 10);
        $result = new ArrayObject();
        $stream = IntPipeline::wrap($values);

        $stream
            ->sorted()
            ->filter(function(int $e) {
                return $e % 2 == 0;
            })
            ->map(function(int $e) : int {
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

    public function testAverage()
    {
        $values = [1, 1, 2, 3, 4, 4];
        $stream = IntPipeline::wrap($values);
        $result = $stream->average();

        $this->assertEquals(2, $result);
    }

    public function testWrapTraversable()
    {
        $pipeline = IntPipeline::wrap(new \ArrayIterator([1, 2, 3]));
        $result   = $pipeline->toArray();

        $this->assertSame([1, 2, 3], $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 passed to Pipeline\IntPipeline::wrap($source) must be an instance of array|\Traversable, stdClass given.
     */
    public function testWrapInvalidObjectArgumentException()
    {
        IntPipeline::wrap(new \stdClass);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 passed to Pipeline\IntPipeline::wrap($source) must be an instance of array|\Traversable, NULL given.
     */
    public function testWrapInvalidNullArgumentException()
    {
        IntPipeline::wrap(null);
    }
}
