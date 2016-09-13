<?php

namespace PipelineTest;

use PipelineTest\TestCase;

use Pipeline\Op\ReduceOp;

class ReduceOpTest extends TestCase
{
    public function testTerminalOp()
    {
        $terminalOp = new ReduceOp(function(int $state, int $e) {
            return $state + $e;
        }, 0);

        $terminalOp->begin();

        $terminalOp->accept(1);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(2);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(3);
        $this->assertFalse($terminalOp->cancellationRequested());

        $this->assertEquals(6, $terminalOp->get());
    }
}
