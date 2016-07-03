<?php

namespace PipelineTest;

use Iterator;
use Pipeline\Stream;
use Pipeline\NumericPipeline;

class NumericPipelineTest extends BaseStreamTest
{
    protected function createStream(Iterator $source) : Stream
    {
        return NumericPipeline::head($source);
    }
}
