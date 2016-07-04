<?php

namespace PipelineTest;

use PipelineTest\TestCase;

use ArrayObject;
use Pipeline\Sink\InvokeSink;

class InvokeSinkTest extends TestCase
{
    public function testSink()
    {
        $calls      = new ArrayObject();
        $downstream = $this->createMock('Pipeline\Sink');
        $sink       = new InvokeSink($downstream, function (int $item) use ($calls) {
            $calls[] = $item;
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

        $sink->accept(1);
        $sink->accept(2);
        $sink->accept(3);

        $sink->end();

        $this->assertFalse($sink->cancellationRequested());
        $this->assertEquals([1, 2, 3], $calls->getArrayCopy());
    }
}
