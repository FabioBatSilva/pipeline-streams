<?php

namespace PipelineTest\Sink;

use PipelineTest\TestCase;

use Pipeline\Sink\SortSink;

class SortSinkTest extends TestCase
{
    public function testSinkAccept()
    {
        $downstream = $this->createMock('Pipeline\Sink');
        $sink       = new SortSink($downstream);

        $downstream
            ->expects($this->never())
            ->method('accept');

        $downstream
            ->expects($this->never())
            ->method('begin');

        $downstream
            ->expects($this->never())
            ->method('end');

        $downstream
            ->expects($this->once())
            ->method('cancellationRequested')
            ->willReturn(false);

        $sink->begin();

        $sink->accept(2);
        $sink->accept(3);
        $sink->accept(1);

        $this->assertFalse($sink->cancellationRequested());
    }

    public function testSinkEnd()
    {
        $downstream = $this->createMock('Pipeline\Sink');
        $sink       = new SortSink($downstream);

        $downstream
            ->expects($this->once())
            ->method('begin');

        $downstream
            ->expects($this->once())
            ->method('end');

        $downstream
            ->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                $this->equalTo(1),
                $this->equalTo(2),
                $this->equalTo(3)
            );

        $downstream
            ->expects($this->exactly(4))
            ->method('cancellationRequested')
            ->willReturn(false);

        $sink->begin();

        $sink->accept(2);
        $sink->accept(3);
        $sink->accept(1);

        $sink->end();

        $this->assertFalse($sink->cancellationRequested());
    }

    public function testSinkCancellation()
    {
        $downstream = $this->createMock('Pipeline\Sink');
        $sink       = new SortSink($downstream);

        $downstream
            ->expects($this->once())
            ->method('begin');

        $downstream
            ->expects($this->once())
            ->method('end');

        $downstream
            ->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                $this->equalTo(1),
                $this->equalTo(2)
            );

        $downstream
            ->expects($this->exactly(4))
            ->method('cancellationRequested')
            ->will($this->onConsecutiveCalls(false, false, true, true));

        $sink->begin();

        $sink->accept(2);
        $sink->accept(3);
        $sink->accept(1);

        $sink->end();

        $this->assertTrue($sink->cancellationRequested());
    }
}
