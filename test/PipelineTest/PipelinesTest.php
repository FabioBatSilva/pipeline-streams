<?php

namespace PipelineTest;

use stdClass;
use ArrayIterator;
use Pipeline\Pipelines;

class PipelinesTest extends TestCase
{
    public function testCreatePipelineOf()
    {
        $objOne   = new stdClass();
        $objTwo   = new stdClass();
        $objThree = new stdClass();

        $stream1 = Pipelines::of(1, 2, 3);
        $stream2 = Pipelines::of('one', 'two', 'three');
        $stream3 = Pipelines::of([1111], [2222], [3333]);
        $stream4 = Pipelines::of($objOne, $objTwo, $objThree);

        $this->assertEquals([1, 2, 3], $stream1->toArray());
        $this->assertEquals(['one', 'two', 'three'], $stream2->toArray());
        $this->assertEquals([[1111], [2222], [3333]], $stream3->toArray());
        $this->assertEquals([$objOne, $objTwo, $objThree], $stream4->toArray());
    }

    public function testWrapValueWithPipeline()
    {
        $array1    = [1, 2, 3];
        $array2    = [
            'one'   => 11,
            'two'   => 22,
            'three' => 33
        ];

        $iterator1 = new ArrayIterator($array1);
        $iterator2 = new ArrayIterator($array2);

        $stream1 = Pipelines::wrap($array1);
        $stream2 = Pipelines::wrap($array2);
        $stream3 = Pipelines::wrap($iterator1);
        $stream4 = Pipelines::wrap($iterator2);

        $this->assertEquals([1, 2, 3], $stream1->toArray());
        $this->assertEquals([11, 22, 33], $stream2->toArray());

        $this->assertEquals([1, 2, 3], $stream3->toArray());
        $this->assertEquals([11, 22, 33], $stream4->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 passed to Pipeline\Pipelines::wrap($source) must be an instance of array|\Iterator, stdClass given.
     */
    public function testWrapInvalidObjectArgumentException()
    {
        Pipelines::wrap(new stdClass);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 passed to Pipeline\Pipelines::wrap($source) must be an instance of array|\Iterator, NULL given.
     */
    public function testWrapInvalidNullArgumentException()
    {
        Pipelines::wrap(null);
    }
}
