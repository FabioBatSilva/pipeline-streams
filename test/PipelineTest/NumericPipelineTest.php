<?php

namespace PipelineTest;

use Iterator;
use Pipeline\NumericPipeline;

class NumericPipelineTest extends AbstractStreamTest
{
    protected function createStream(Iterator $source)
    {
        return NumericPipeline::head($source);
    }
}
