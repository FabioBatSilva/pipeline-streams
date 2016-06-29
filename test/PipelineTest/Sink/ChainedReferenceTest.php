<?php

namespace PipelineTest;

use PipelineTest\TestCase;

use Pipeline\Sink\ChainedReference;

class ChainedReferenceTest extends TestCase
{
    public function testChainInvocations()
    {
        $sink  = $this->createMock('Pipeline\Sink');
        $chain = new ChainedReference($sink);

        $sink
            ->expects($this->once())
            ->method('cancellationRequested')
            ->willReturn(false);

        $sink
            ->expects($this->once())
            ->method('begin')
            ->with($this->equalTo(3));

        $sink
            ->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                $this->equalTo('one'),
                $this->equalTo('two'),
                $this->equalTo('three')
            );

        $sink
            ->expects($this->once())
            ->method('end');

        $chain->begin(3);

        $chain->accept('one');
        $chain->accept('two');
        $chain->accept('three');

        $chain->end();

        $this->assertFalse($chain->cancellationRequested());
    }
}
