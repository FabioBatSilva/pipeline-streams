<?php

namespace PipelineTest\Sink;

use PipelineTest\TestCase;

use Pipeline\Sink\MapSink;

class MapSinkTest extends TestCase
{
    public function testSink()
    {
        $downstream = $this->createMock('Pipeline\Sink');
        $sink       = new MapSink($downstream, function(string $item) {
            return strtoupper($item);
        });

        $downstream
            ->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                $this->equalTo('ONE'),
                $this->equalTo('TWO'),
                $this->equalTo('THREE')
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

        $sink->accept('one');
        $sink->accept('two');
        $sink->accept('three');

        $sink->end();

        $this->assertFalse($sink->cancellationRequested());
    }
}
