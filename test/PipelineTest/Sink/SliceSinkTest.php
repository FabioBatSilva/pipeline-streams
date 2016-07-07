<?php

namespace PipelineTest\Sink;

use PipelineTest\TestCase;

use Pipeline\Sink\SliceSink;

class SliceSinkTest extends TestCase
{
    public function testSinkSkip()
    {
        $downstream = $this->createMock('Pipeline\Sink');
        $sink       = new SliceSink($downstream, 2);

        $downstream
            ->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                $this->equalTo(3),
                $this->equalTo(4),
                $this->equalTo(5)
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
        $sink->accept(4);
        $sink->accept(5);

        $sink->end();

        $this->assertFalse($sink->cancellationRequested());
    }

    public function testSinkLimit()
    {
        $downstream = $this->createMock('Pipeline\Sink');
        $sink       = new SliceSink($downstream, null, 3);

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
        $sink->accept(2);

        $this->assertFalse($sink->cancellationRequested());

        $sink->accept(3);

        $this->assertTrue($sink->cancellationRequested());

        $sink->accept(4);
        $sink->accept(5);

        $sink->end();
    }
}
