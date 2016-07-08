<?php

namespace PipelineTest;

use ArrayObject;
use Pipeline\Pipeline;
use Pipeline\Collectors;

class StreamCollectTest extends TestCase
{
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

        $stream   = Pipeline::wrap($values);
        $result   = $stream->collect(Collectors::groupingBy(function (array $item){
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

    public function testCollectGroupByMapping()
    {
        $values = [
            ['key' => 'one',   'value' => [1, 1]],
            ['key' => 'one',   'value' => [1, 2]],
            ['key' => 'one',   'value' => [1, 3]],
            ['key' => 'two',   'value' => [2, 1]],
            ['key' => 'two',   'value' => [2, 2]],
            ['key' => 'three', 'value' => [3, 1]]
        ];

        $mapValues = Collectors::mapping(function (array $item) {
            return implode(' - ', $item['value']);
        });

        $groupByKey = Collectors::groupingBy(function (array $item) {
            return $item['key'];
        }, $mapValues);

        $stream = Pipeline::wrap($values);
        $result = $stream->collect($groupByKey, $mapValues);

        $this->assertArrayHasKey('one', $result);
        $this->assertArrayHasKey('two', $result);
        $this->assertArrayHasKey('three', $result);

        $this->assertCount(3, $result['one']);
        $this->assertCount(2, $result['two']);
        $this->assertCount(1, $result['three']);

        $this->assertEquals('1 - 1', $result['one'][0]);
        $this->assertEquals('1 - 2', $result['one'][1]);
        $this->assertEquals('1 - 3', $result['one'][2]);

        $this->assertEquals('2 - 1', $result['two'][0]);
        $this->assertEquals('2 - 2', $result['two'][1]);

        $this->assertEquals('3 - 1', $result['three'][0]);
    }

    public function testCollectMapping()
    {
        $values = [
            ['value' => [1, 1]],
            ['value' => [1, 2]],
            ['value' => [2, 1]],
            ['value' => [2, 2]]
        ];

        $collector = Collectors::mapping(function (array $item){
            return implode(' - ', $item['value']);
        });

        $stream = Pipeline::wrap($values);
        $result = $stream->collect($collector);

        $this->assertCount(4, $result);

        $this->assertEquals('1 - 1', $result[0]);
        $this->assertEquals('1 - 2', $result[1]);
        $this->assertEquals('2 - 1', $result[2]);
        $this->assertEquals('2 - 2', $result[3]);
    }

    public function testCollectJoining()
    {
        $values  = range(1, 5);
        $stream1 = Pipeline::wrap($values);
        $stream2 = Pipeline::wrap($values);
        $result1 = $stream1->collect(Collectors::joining());
        $result2 = $stream2->collect(Collectors::joining(' - '));

        $this->assertEquals('1,2,3,4,5', $result1);
        $this->assertEquals('1 - 2 - 3 - 4 - 5', $result2);
    }
}
