<?php

namespace PipelineTest\Sink;

use PipelineTest\TestCase;

use Pipeline\Sink\FlatMapSink;

class FlatMapSinkTest extends TestCase
{
    public function testSink()
    {
        $downstream = $this->createMock('Pipeline\Sink');
        $sink       = new FlatMapSink($downstream, function(array $item) {
            return $item['values'];
        });

        $downstream
            ->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                $this->equalTo(1),
                $this->equalTo(2),
                $this->equalTo(3)
            );

        $downstream
            ->expects($this->once())
            ->method('cancellationRequested')
            ->willReturn(false);

        $downstream
            ->expects($this->once())
            ->method('begin');

        $downstream
            ->expects($this->once())
            ->method('end');

        $sink->begin();

        $sink->accept([
            'values' => [1, 2]
        ]);

        $sink->accept([
            'values' => null
        ]);

        $sink->accept([
            'values' => [3]
        ]);

        $sink->end();

        $this->assertFalse($sink->cancellationRequested());
    }
}
