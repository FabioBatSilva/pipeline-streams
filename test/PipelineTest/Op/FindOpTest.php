<?php

namespace PipelineTest;

use PipelineTest\TestCase;

use Pipeline\Op\FindOp;

class FindOpTest extends TestCase
{
    public function testTerminalOpFindFirst()
    {
        $terminalOp = new FindOp();

        $terminalOp->begin();

        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(1);

        $this->assertTrue($terminalOp->cancellationRequested());

        $this->assertEquals(1, $terminalOp->get());
    }

    public function testTerminalOpFindCallable()
    {
        $terminalOp = new FindOp(function (int $e) {
            return $e === 3;
        });

        $terminalOp->begin();

        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(1);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(2);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(3);
        $this->assertTrue($terminalOp->cancellationRequested());

        $this->assertEquals(3, $terminalOp->get());
    }
}
