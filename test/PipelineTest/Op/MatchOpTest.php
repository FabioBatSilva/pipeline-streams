<?php

namespace PipelineTest;

use PipelineTest\TestCase;

use Pipeline\Op\MatchOp;

class MatchOpTest extends TestCase
{
    public function testTerminalOpMatchAnySuccess()
    {
        $terminalOp = new MatchOp(function(int $e) {
            return $e % 2 == 0;
        }, MatchOp::ANY);

        $terminalOp->begin();

        $terminalOp->accept(1);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(3);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(2);
        $this->assertTrue($terminalOp->cancellationRequested());

        $this->assertTrue($terminalOp->get());
    }

    public function testTerminalOpMatchAnyFail()
    {
        $terminalOp = new MatchOp(function(int $e) {
            return $e % 2 == 0;
        }, MatchOp::ANY);

        $terminalOp->begin();

        $terminalOp->accept(1);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(3);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(5);
        $this->assertFalse($terminalOp->cancellationRequested());

        $this->assertFalse($terminalOp->get());
    }

    public function testTerminalOpMatchAllSuccess()
    {
        $terminalOp = new MatchOp(function(int $e) {
            return $e % 2 == 0;
        }, MatchOp::ALL);

        $terminalOp->begin();

        $terminalOp->accept(2);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(4);
        $this->assertFalse($terminalOp->cancellationRequested());

        $this->assertTrue($terminalOp->get());
    }

    public function testTerminalOpMatchAllFail()
    {
        $terminalOp = new MatchOp(function(int $e) {
            return $e % 2 == 0;
        }, MatchOp::ALL);

        $terminalOp->begin();

        $terminalOp->accept(2);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(4);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(5);
        $this->assertTrue($terminalOp->cancellationRequested());

        $this->assertFalse($terminalOp->get());
    }

    public function testTerminalOpMatchNoneSuccess()
    {
        $terminalOp = new MatchOp(function(int $e) {
            return $e % 2 == 0;
        }, MatchOp::NONE);

        $terminalOp->begin();

        $terminalOp->accept(1);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(3);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(5);
        $this->assertFalse($terminalOp->cancellationRequested());

        $this->assertTrue($terminalOp->get());
    }

    public function testTerminalOpMatchNoneFail()
    {
        $terminalOp = new MatchOp(function(int $e) {
            return $e % 2 == 0;
        }, MatchOp::NONE);

        $terminalOp->begin();

        $terminalOp->accept(1);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(3);
        $this->assertFalse($terminalOp->cancellationRequested());

        $terminalOp->accept(4);
        $this->assertTrue($terminalOp->cancellationRequested());

        $this->assertFalse($terminalOp->get());
    }
}
