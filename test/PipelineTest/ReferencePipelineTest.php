<?php

namespace PipelineTest;

use ArrayObject;
use ArrayIterator;
use Pipeline\ReferencePipeline;

class ReferencePipelineTest extends TestCase
{
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

                var_dump($e);
            });

        $this->assertCount(2, $result);
        $this->assertEquals(4, $result[0]);
        $this->assertEquals(8, $result[1]);
    }
}
