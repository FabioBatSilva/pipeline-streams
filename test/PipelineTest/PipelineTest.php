<?php

namespace PipelineTest;

use ArrayObject;
use Pipeline\Pipeline;
use Pipeline\Collector;
use Pipeline\Collectors;

class PipelineTest extends TestCase
{
    public function testReducePipeline()
    {
        $stream = Pipeline::of(2, 4, 8, 18, 32);
        $result = $stream->reduce(0, function(int $state, int $item) {
            return $state + $item;
        });

        $this->assertSame(64, $result);
    }

    public function testToArray()
    {
        $stream = Pipeline::wrap(range(1, 10));
        $result = $stream->filter(function(int $e) {
            return $e % 2 == 0;
        })
        ->toArray();

        $this->assertSame([2, 4, 6, 8, 10], $result);
    }

    public function testFlatMap()
    {
        $stream = Pipeline::of(1, 2, 3);
        $result = $stream->flatMap(function(int $e) : array {
            return [$e * 1, $e * 2, $e * 3];
        })->toArray();

        $this->assertSame([1, 2, 3, 2, 4, 6, 3, 6, 9], $result);
    }

    public function testSorted()
    {
        $stream = Pipeline::of(5, 4, 3, 2, 1);
        $result = $stream
            ->sorted()
            ->toArray();

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testSortedWithFunction()
    {
        $stream = Pipeline::of(2, 3, 1);
        $result = $stream->sorted(function (int $a, int $b) {
            return $a <=> $b;
        })->toArray();

        $this->assertSame([1, 2, 3], $result);
    }

    public function testLimit()
    {
        $values = range(1, 10);
        $stream = Pipeline::wrap($values);
        $result = $stream
            ->limit(5)
            ->toArray();

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testSkip()
    {
        $values = range(1, 10);
        $stream = Pipeline::wrap($values);
        $result = $stream
            ->skip(5)
            ->toArray();

        $this->assertSame([6, 7, 8, 9, 10], $result);
    }

    public function testSlice()
    {
        $values = range(1, 10);
        $stream = Pipeline::wrap($values);
        $result = $stream
            ->skip(4)
            ->limit(4)
            ->toArray();

        $this->assertSame([5, 6, 7, 8], $result);
    }

    public function testMin()
    {
        $stream = Pipeline::of(1, 5, 5, 3, 10);
        $result = $stream->min();

        $this->assertSame(1, $result);
    }

    public function testMax()
    {
        $stream = Pipeline::of(1, 7, 5, 5, 2);
        $result = $stream->max();

        $this->assertSame(7, $result);
    }

    public function testCount()
    {
        $values = range(1, 10);
        $stream = Pipeline::wrap($values);
        $result = $stream->count();

        $this->assertEquals(10, $result);
    }

    public function testFindFirst()
    {
        $stream = Pipeline::of(5, 4, 3, 2, 1);
        $result = $stream->findFirst();

        $this->assertEquals(5, $result);
    }

    public function testFindFirstMatch()
    {
        $stream = Pipeline::of(5, 4, 3, 2, 1);
        $result = $stream->findFirst(function(int $e) {
            return ($e < 5) && ($e % 2 != 0);
        });

        $this->assertEquals(3, $result);
    }

    public function testAnyMatch()
    {
        $values  = [5, 4, 3, 2, 1];
        $stream1 = Pipeline::wrap($values);
        $stream2 = Pipeline::wrap($values);

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
        $stream1 = Pipeline::wrap($values);
        $stream2 = Pipeline::wrap($values);

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
        $stream1 = Pipeline::wrap($values);
        $stream2 = Pipeline::wrap($values);

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
        $stream    = Pipeline::of(1, 2, 3);
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
        $stream = Pipeline::wrap($values);
        $result = $stream->distinct()->toArray();

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testPeek()
    {
        $peeks  = new ArrayObject();
        $values = [1, 2, 3, 4, 5];
        $stream = Pipeline::wrap($values);

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
        $stream = Pipeline::wrap($values);

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

    public function testMapToInt()
    {
        $values = ['a', 'bb', 'ccc'];
        $stream = Pipeline::wrap($values);
        $result = $stream->mapToInt(function(string $e) {
            return strlen($e);
        })->sum();

        $this->assertEquals(6, $result);
    }

    public function testMapToFloat()
    {
        $values = ['a', 'bb', 'ccc'];
        $stream = Pipeline::wrap($values);
        $result = $stream->mapToFloat(function(string $e) {
            return strlen($e) * 1.5;
        })->sum();

        $this->assertSame(9.0, $result);
    }

    public function testFlatMapToInt()
    {
        $values = [['a', 'bb'], ['ccc', 'dddd']];
        $stream = Pipeline::wrap($values);
        $result = $stream->flatMapToInt(function(array $e) {
            return array_map('strlen', $e);
        })->average();

        $this->assertEquals(2, $result);
    }

    public function testFlatMapToFloat()
    {
        $stream   = Pipeline::wrap(['one', 'two', 'three']);
        $result = $stream->flatMapToFloat(function(string $e) : array {
            $length = strlen($e);
            $value1 = $length * 1.5;
            $value2 = $length * 2.5;

            return [$value1, $value2];
        })->toArray();

        $this->assertSame([4.5, 7.5, 4.5, 7.5, 7.5, 12.5], $result);
    }

    public function testPeekMap()
    {
        $values   = ['one', 'two', 'three', 'five'];
        $stream   = Pipeline::wrap($values);
        $result   = new ArrayObject();
        $mapping  = [
            'ONE'   => 11,
            'TWO'   => 22,
            'THREE' => 33
        ];

        $sum = $stream
            ->map(function(string $e) {
                return strtoupper($e);
            })
            ->filter(function(string $e) use ($mapping) {
                return isset($mapping[$e]);
            })
            ->mapToInt(function(string $e) use ($mapping) {
                return $mapping[$e];
            })
            ->peek(function(int $e) use ($result) {
                $result[] = $e;
            })
            ->filter(function(int $e) {
                return ($e % 2 != 0);
            })
            ->sum();

        $this->assertEquals(44, $sum);
        $this->assertCount(3, $result);
        $this->assertEquals([11, 22, 33], $result->getArrayCopy());
    }

    public function testCountWorks()
    {
        $readFileLines = function ($file) : \Iterator {
            $file = new \SplFileObject($file);

            while ( ! $file->eof()) {
                yield $file->fgets();
            }
        };

        $file   = __DIR__ . '/../../LICENSE';
        $lines  = $readFileLines($file);
        $result = Pipeline::wrap($lines)
            ->filter(function(string $line) {
                return strlen($line) > 1;
            })
            ->map(function(string $line) {
                return trim(strtolower($line));
            })
            ->flatMap(function(string $line) {
                return explode(' ', $line);
            })
            ->collect(Collectors::groupingBy(function (string $word) {
                return $word;
            }, Collectors::counting()));

        $this->assertCount(100, $result);
        $this->assertEquals(13, $result['the']);
        $this->assertEquals(3, $result['copyright']);
        $this->assertEquals(2, $result['permission']);
    }

    public function testWrapIterator()
    {
        $pipeline = Pipeline::wrap(new \ArrayIterator([1, 2, 3]));
        $result   = $pipeline->toArray();

        $this->assertSame([1, 2, 3], $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 passed to Pipeline\Pipeline::wrap($source) must be an instance of array|\Iterator, stdClass given.
     */
    public function testWrapInvalidObjectArgumentException()
    {
        Pipeline::wrap(new \stdClass);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 passed to Pipeline\Pipeline::wrap($source) must be an instance of array|\Iterator, NULL given.
     */
    public function testWrapInvalidNullArgumentException()
    {
        Pipeline::wrap(null);
    }
}
