<?php

namespace PipelineTest\Sink;

use PipelineTest\TestCase;

use Pipeline\Sink\FilterSink;

class FilterSinkTest extends TestCase
{
    public function testSink()
    {
        $downstream = $this->createMock('Pipeline\Sink');
        $sink       = new FilterSink($downstream, function(int $e) {
            return ($e % 2 != 0);
        });

        $downstream
            ->expects($this->exactly(2))
            ->method('accept')
            ->withConsecutive(
                $this->equalTo(1),
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

        $sink->accept(1);
        $sink->accept(2);
        $sink->accept(3);

        $sink->end();

        $this->assertFalse($sink->cancellationRequested());
    }
}
