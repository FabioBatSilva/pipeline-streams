<?php

namespace PipelineTest;

use PipelineTest\TestCase;

use Pipeline\Op\CollectOp;

class CollectOpTest extends TestCase
{
    public function testTerminalOp()
    {
        $collector  = $this->createMock('Pipeline\Collector');
        $terminalOp = new CollectOp($collector);
        $state      = [1, 2];

        $collector
            ->expects($this->once())
            ->method('begin')
            ->willReturn($state);

        $collector
            ->expects($this->exactly(2))
            ->method('accept')
            ->withConsecutive(
                [$this->equalTo($state), $this->equalTo(3)],
                [$this->equalTo($state), $this->equalTo(4)]
            );

        $collector
            ->expects($this->once())
            ->method('finish')
            ->with($this->equalTo([1, 2]))
            ->willReturn('1,2,3,4');

        $terminalOp->begin();
        $terminalOp->accept(3);
        $terminalOp->accept(4);

        $this->assertEquals('1,2,3,4', $terminalOp->get());
        $this->assertFalse($terminalOp->cancellationRequested());
    }
}
