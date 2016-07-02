<?php

namespace PipelineTest;

use ArrayObject;
use ArrayIterator;
use Pipeline\Pipelines;
use Pipeline\Collector;
use Pipeline\Collectors;
use Pipeline\ReferencePipeline;

class PipelineTest extends TestCase
{
    public function testReducePipeline()
    {
        $stream = Pipelines::of(2, 4, 8, 18, 32);
        $result = $stream->reduce(function(int $item, $state) {
            return $state + $item;
        }, 0);

        $this->assertEquals(64, $result);
    }

    public function testToArray()
    {
        $stream = Pipelines::of(...range(1, 10));
        $result = $stream->filter(function(int $e) {
            return $e % 2 == 0;
        })
        ->toArray();

        $this->assertEquals([2, 4, 6, 8, 10], $result);
    }

    public function testFlatMap()
    {
        $stream = Pipelines::of([1,2,3], [4,5,6], [7,8,9]);
        $result = $stream->flatMap(function(array $e) {
            return $e;
        })->toArray();

        $this->assertEquals([1,2,3,4,5,6,7,8,9], $result);
    }

    public function testSorted()
    {
        $stream = Pipelines::of(5, 4, 3, 2, 1);
        $result = $stream
            ->sorted()
            ->toArray();

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testSortedWithFunction()
    {
        $stream = Pipelines::of('lemons', 'apples', 'grapes');
        $result = $stream->sorted(function (string $a, string $b) {
            return strcmp($a, $b);
        })->toArray();

        $this->assertEquals(['apples', 'grapes', 'lemons'], $result);
    }

    public function testLimit()
    {
        $stream = Pipelines::of(...range(1, 10));
        $result = $stream
            ->limit(5)
            ->toArray();

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testSkip()
    {
        $stream = Pipelines::of(...range(1, 10));
        $result = $stream
            ->skip(5)
            ->toArray();

        $this->assertEquals([6, 7, 8, 9, 10], $result);
    }

    public function testSlice()
    {
        $stream = Pipelines::of(...range(1, 10));
        $result = $stream
            ->skip(4)
            ->limit(4)
            ->toArray();

        $this->assertEquals([5, 6, 7, 8], $result);
    }

    public function testMin()
    {
        $stream = Pipelines::of(1, 5, 5, 3, 10);
        $result = $stream->min();

        $this->assertEquals(1, $result);
    }

    public function testMax()
    {
        $stream = Pipelines::of(1, 7, 5, 5, 2);
        $result = $stream->max();

        $this->assertEquals(7, $result);
    }

    public function testCount()
    {
        $stream = Pipelines::of(...range(1, 10));
        $result = $stream->count();

        $this->assertEquals(10, $result);
    }

    public function testFindFirst()
    {
        $stream = Pipelines::of(5, 4, 3, 2, 1);
        $result = $stream->findFirst();

        $this->assertEquals(5, $result);
    }

    public function testFindFirstMatch()
    {
        $stream = Pipelines::of(5, 4, 3, 2, 1);
        $result = $stream->findFirst(function(int $e) {
            return ($e < 5) && ($e % 2 != 0);
        });

        $this->assertEquals(3, $result);
    }

    public function testAnyMatch()
    {
        $values  = [5, 4, 3, 2, 1];
        $stream1 = Pipelines::of(...$values);
        $stream2 = Pipelines::of(...$values);

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
        $stream1 = Pipelines::of(...$values);
        $stream2 = Pipelines::of(...$values);

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
        $stream1 = Pipelines::of(...$values);
        $stream2 = Pipelines::of(...$values);

        $this->assertTrue($stream1->noneMatch(function(int $e) {
            return $e > 10;
        }));

        $this->assertFalse($stream2->noneMatch(function(int $e) {
            return $e < 5;
        }));
    }

    public function testCollect()
    {
        $stream    = Pipelines::of('one', 'two', 'three');
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

        $this->assertEquals('foo-bar', $stream->collect($collector));
    }

    public function testCollectGroupBy()
    {
        $values = [
            ['key' => 'one',   'value' => '1 - 1'],
            ['key' => 'one',   'value' => '1 - 2'],
            ['key' => 'one',   'value' => '1 - 3'],
            ['key' => 'two',   'value' => '2 - 1'],
            ['key' => 'two',   'value' => '2 - 2'],
            ['key' => 'three', 'value' => '3 - 1']
        ];

        $stream = Pipelines::of(...$values);
        $result = $stream->collect(Collectors::groupingBy(function (array $item){
            return $item['key'];
        }));

        $this->assertArrayHasKey('one', $result);
        $this->assertArrayHasKey('two', $result);
        $this->assertArrayHasKey('three', $result);

        $this->assertCount(3, $result['one']);
        $this->assertCount(2, $result['two']);
        $this->assertCount(1, $result['three']);

        $this->assertEquals('1 - 1', $result['one'][0]['value']);
        $this->assertEquals('1 - 2', $result['one'][1]['value']);
        $this->assertEquals('1 - 3', $result['one'][2]['value']);

        $this->assertEquals('2 - 1', $result['two'][0]['value']);
        $this->assertEquals('2 - 2', $result['two'][1]['value']);

        $this->assertEquals('3 - 1', $result['three'][0]['value']);
    }

    public function testMapToNumeric()
    {
        $stream = Pipelines::of('a', 'bb', 'ccc');
        $result = $stream->mapToNumeric(function(string $e) {
            return strlen($e);
        })->sum();

        $this->assertEquals(6, $result);
    }

    public function testFlatMapToNumeric()
    {
        $stream = Pipelines::of(['a', 'bb'], ['ccc', 'dddd']);
        $result = $stream->flatMapToNumeric(function(array $e) {
            return array_map('strlen', $e);
        })->average();

        $this->assertEquals(2.5, $result);
    }

    public function testDistinct()
    {
        $stream = Pipelines::of(1, 2, 2, 3, 4, 5, 5, 1);
        $result = $stream->distinct()->toArray();

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testForEach()
    {
        $stream = Pipelines::of(...array_reverse(range(0, 10)));
        $result = new ArrayObject();

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
