<?php

namespace PipelineTest;

use PipelineTest\TestCase;

use ArrayObject;
use Pipeline\Op\ForEachOp;

class ForEachOpTest extends TestCase
{
    public function testTerminalOp()
    {
        $calls      = new ArrayObject();
        $terminalOp = new ForEachOp(function(int $e) use ($calls) {
            $calls[] = $e;
        });

        $terminalOp->begin();
        $terminalOp->accept(1);
        $terminalOp->accept(2);
        $terminalOp->accept(3);

        $this->assertEquals([1, 2, 3], $calls->getArrayCopy());

        $this->assertNull($terminalOp->get());
        $this->assertFalse($terminalOp->cancellationRequested());
    }
}
