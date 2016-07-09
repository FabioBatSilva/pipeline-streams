<?php

namespace PipelineTest;

use Traversable;
use ArrayObject;
use ArrayIterator;
use Pipeline\Stream;
use Pipeline\Collector;
use Pipeline\FloatPipeline;

class FloatPipelineTest extends TestCase
{
    public function testFluentInterface()
    {
        $stream = FloatPipeline::wrap([]);

        $this->assertInstanceOf('Pipeline\FloatPipeline', $stream->sorted());
        $this->assertInstanceOf('Pipeline\FloatPipeline', $stream->skip(10));
        $this->assertInstanceOf('Pipeline\FloatPipeline', $stream->distinct());
        $this->assertInstanceOf('Pipeline\FloatPipeline', $stream->map('floatval'));
        $this->assertInstanceOf('Pipeline\FloatPipeline', $stream->peek('var_dump'));
        $this->assertInstanceOf('Pipeline\FloatPipeline', $stream->filter('is_float'));
        $this->assertInstanceOf('Pipeline\FloatPipeline', $stream->flatMap(function(float $f){
            return [$f];
        }));

        $this->assertInstanceOf('Pipeline\IntPipeline', $stream->mapToInt('intval'));
        $this->assertInstanceOf('Pipeline\IntPipeline', $stream->flatMapToInt(function(float $f){
            return [intval($f)];
        }));
    }

    public function testMap()
    {
        $stream = FloatPipeline::of(1.0, 2.0, 3.0);
        $result = $stream->map(function(float $e) : float {
            return $e * $e;
        })->toArray();

        $this->assertSame([1.0, 4.0, 9.0], $result);
    }

    public function testReducePipeline()
    {
        $stream = FloatPipeline::of(2.0, 4.0, 8.0, 18.0, 32.0);
        $result = $stream->reduce(0.0, function(float $state, float $item) {
            return $state + $item;
        });

        $this->assertSame(64.0, $result);
    }

    public function testToArray()
    {
        $stream = FloatPipeline::wrap(array_map('floatval', range(1, 10)));
        $result = $stream->filter(function(float $e) {
            return intval($e % 2) == 0;
        })
        ->toArray();

        $this->assertSame([2.0, 4.0, 6.0, 8.0, 10.0], $result);
    }

    public function testFlatMap()
    {
        $stream = FloatPipeline::of(1.0, 2.0, 3.0);
        $result = $stream->flatMap(function(float $e) : array {
            return [$e * 1, $e * 2, $e * 3];
        })->toArray();

        $this->assertSame([1.0, 2.0, 3.0, 2.0, 4.0, 6.0, 3.0, 6.0, 9.0], $result);
    }

    public function testSorted()
    {
        $stream = FloatPipeline::of(5.0, 4.0, 3.0, 2.0, 1.0);
        $result = $stream
            ->sorted()
            ->toArray();

        $this->assertSame([1.0, 2.0, 3.0, 4.0, 5.0], $result);
    }

    public function testSortedWithFunction()
    {
        $stream = FloatPipeline::of(2.0, 3.0, 1.0);
        $result = $stream->sorted(function (float $a, float $b) {
            return $a <=> $b;
        })->toArray();

        $this->assertSame([1.0, 2.0, 3.0], $result);
    }

    public function testLimit()
    {
        $values = array_map('floatval', range(1, 10));
        $stream = FloatPipeline::wrap($values);
        $result = $stream
            ->limit(5)
            ->toArray();

        $this->assertSame([1.0, 2.0, 3.0, 4.0, 5.0], $result);
    }

    public function testSkip()
    {
        $values = array_map('floatval', range(1, 10));
        $stream = FloatPipeline::wrap($values);
        $result = $stream
            ->skip(5)
            ->toArray();

        $this->assertSame([6.0, 7.0, 8.0, 9.0, 10.0], $result);
    }

    public function testSlice()
    {
        $values = array_map('floatval', range(1, 10));
        $stream = FloatPipeline::wrap($values);
        $result = $stream
            ->skip(4)
            ->limit(4)
            ->toArray();

        $this->assertSame([5.0, 6.0, 7.0, 8.0], $result);
    }

    public function testMin()
    {
        $stream = FloatPipeline::of(1.0, 5.0, 5.0, 3.0, 10.0);
        $result = $stream->min();

        $this->assertSame(1.0, $result);
    }

    public function testMax()
    {
        $stream = FloatPipeline::of(1.0, 7.0, 5.0, 5.0, 2.0);
        $result = $stream->max();

        $this->assertSame(7.0, $result);
    }

    public function testCount()
    {
        $values = array_map('floatval', range(1, 10));
        $stream = FloatPipeline::wrap($values);
        $result = $stream->count();

        $this->assertEquals(10.0, $result);
    }

    public function testFindFirst()
    {
        $stream = FloatPipeline::of(5.0, 4.0, 3.0, 2.0, 1.0);
        $result = $stream->findFirst();

        $this->assertEquals(5.0, $result);
    }

    public function testFindFirstMatch()
    {
        $stream = FloatPipeline::of(5.0, 4.0, 3.0, 2.0, 1.0);
        $result = $stream->findFirst(function(float $e) {
            return ($e < 5) && (intval($e % 2) != 0);
        });

        $this->assertEquals(3.0, $result);
    }

    public function testAnyMatch()
    {
        $values  = [5.0, 4.0, 3.0, 2.0, 1.0];
        $stream1 = FloatPipeline::wrap($values);
        $stream2 = FloatPipeline::wrap($values);

        $this->assertTrue($stream1->anyMatch(function(float $e) {
            return $e === 3.0;
        }));

        $this->assertFalse($stream2->anyMatch(function(float $e) {
            return $e === 10.0;
        }));
    }

    public function testAllMatch()
    {
        $values  = [5.0, 4.0, 3.0, 2.0, 1.0];
        $stream1 = FloatPipeline::wrap($values);
        $stream2 = FloatPipeline::wrap($values);

        $this->assertTrue($stream1->allMatch(function(float $e) {
            return $e < 10.0;
        }));

        $this->assertFalse($stream2->allMatch(function(float $e) {
            return $e > 2.0;
        }));
    }

    public function testNoneMatch()
    {
        $values  = [5.0, 4.0, 3.0, 2.0, 1.0];
        $stream1 = FloatPipeline::wrap($values);
        $stream2 = FloatPipeline::wrap($values);

        $this->assertTrue($stream1->noneMatch(function(float $e) {
            return $e > 10.0;
        }));

        $this->assertFalse($stream2->noneMatch(function(float $e) {
            return $e < 5.0;
        }));
    }

    public function testCollect()
    {
        $values    = [1.0, 2.0, 3.0];
        $stream    = FloatPipeline::of(1.0, 2.0, 3.0);
        $collector = $this->createMock(Collector::CLASS);

        $collector
            ->expects($this->once())
            ->method('begin')
            ->willReturn(0);

        $collector
            ->expects($this->once())
            ->method('finish')
            ->willReturn(6.0);

        $collector
            ->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                [$this->equalTo(0), $this->equalTo(1.0)],
                [$this->equalTo(0), $this->equalTo(2.0)],
                [$this->equalTo(0), $this->equalTo(3.0)]
            );

        $this->assertSame(6.0, $stream->collect($collector));
    }

    public function testDistinct()
    {
        $values = [1.0, 2.0, 2.0, 3.0, 4.0, 5.0, 5.0, 1.0];
        $stream = FloatPipeline::wrap($values);
        $result = $stream->distinct()->toArray();

        $this->assertSame([1.0, 2.0, 3.0, 4.0, 5.0], $result);
    }

    public function testPeek()
    {
        $peeks  = new ArrayObject();
        $values = [1.0, 2.0, 3.0, 4.0, 5.0];
        $stream = FloatPipeline::wrap($values);

        $result = $stream
            ->peek(function(float $e) use ($peeks) {
                $peeks[] = $e;
            })
            ->filter(function(float $e) {
                return (intval($e % 2) != 0);
            })
            ->toArray();

        $this->assertCount(5, $peeks);
        $this->assertCount(3, $result);
        $this->assertSame([1.0, 3.0, 5.0], $result);
        $this->assertSame([1.0, 2.0, 3.0, 4.0, 5.0], $peeks->getArrayCopy());
    }

    public function testForEach()
    {
        $values = array_map('floatval', range(1, 10));
        $stream = FloatPipeline::wrap($values);
        $result = new ArrayObject();

        $stream
            ->sorted()
            ->filter(function(float $e) {
                return intval($e % 2) == 0;
            })
            ->map(function(float $e) : float {
                return $e + $e;
            })
            ->filter(function(float $e) {
                return ($e > 0 && $e < 10);
            })
            ->forEach(function(float $e) use ($result) {
                $result[] = $e;
            });

        $this->assertCount(2, $result);
        $this->assertSame(4.0, $result[0]);
        $this->assertSame(8.0, $result[1]);
    }

    public function testAverage()
    {
        $values = [1.0, 1.0, 2.0, 3.0, 4.0, 4.0];
        $stream = FloatPipeline::wrap($values);
        $result = $stream->average();

        $this->assertEquals(2.5, $result);
    }

    public function testWrapTraversable()
    {
        $pipeline = FloatPipeline::wrap(new \ArrayIterator([1.0, 2.0, 3.0]));
        $result   = $pipeline->toArray();

        $this->assertSame([1.0, 2.0, 3.0], $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 passed to Pipeline\FloatPipeline::wrap($source) must be an instance of array|\Traversable, stdClass given.
     */
    public function testWrapInvalidObjectArgumentException()
    {
        FloatPipeline::wrap(new \stdClass);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 passed to Pipeline\FloatPipeline::wrap($source) must be an instance of array|\Traversable, NULL given.
     */
    public function testWrapInvalidNullArgumentException()
    {
        FloatPipeline::wrap(null);
    }
}
