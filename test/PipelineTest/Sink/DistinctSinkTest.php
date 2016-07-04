<?php

namespace PipelineTest;

use PipelineTest\TestCase;

use Pipeline\Sink\DistinctSink;

class DistinctSinkTest extends TestCase
{
    public function testSink()
    {
        $downstream = $this->createMock('Pipeline\Sink');
        $sink       = new DistinctSink($downstream);

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

        $sink->accept(1);
        $sink->accept(1);
        $sink->accept(2);
        $sink->accept(3);
        $sink->accept(3);
        $sink->accept(1);

        $sink->end();

        $this->assertFalse($sink->cancellationRequested());
    }
}
