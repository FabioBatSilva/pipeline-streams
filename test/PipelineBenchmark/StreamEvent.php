<?php

namespace PipelineBenchmark;

use Iterator;
use Pipeline\Streams;
use Athletic\AthleticEvent;

class StreamEvent extends AthleticEvent
{
    public function createIntIterator(int $size) : Iterator
    {
        for ($i=0; $i < $size; $i++) {
            yield $i;
        }
    }

    /**
     * @iterations 1000
     */
    public function filter()
    {
        $iterator = $this->createIntIterator(1000);
        $stream   = Streams::wrap($iterator);
        $result   = $stream
            ->filter(function(int $e) {
                return $e % 2 == 0;
            })
            ->count();

        assert($result === 500);
    }

    /**
     * @iterations 1000
     */
    public function filterMap()
    {
        $iterator = $this->createIntIterator(1000);
        $stream   = Streams::wrap($iterator);
        $result   = $stream
            ->filter(function(int $e) {
                return $e % 2 == 0;
            })->map(function(int $e) {
                return $e * 10;
            })
            ->count();

        assert($result === 500);
    }

    /**
     * @iterations 1000
     */
    public function filterMapPeek()
    {
        $iterator = $this->createIntIterator(1000);
        $stream   = Streams::wrap($iterator);
        $peeks    = (object) ['count' => 0];
        $result   = $stream
            ->filter(function(int $e) {
                return $e % 2 == 0;
            })->peek(function(int $e) use ($peeks) {
                $peeks->count ++;
            })->map(function(int $e) {
                return $e * 10;
            })
            ->count();

        assert($peeks->count === 500);
        assert($result === 500);
    }

    /**
     * @iterations 1000
     */
    public function filterFlatMap()
    {
        $iterator = $this->createIntIterator(1000);
        $stream   = Streams::wrap($iterator);
        $result   = $stream
            ->filter(function(int $e) {
                return $e % 2 == 0;
            })->flatMapToNumeric(function(int $e) {
                return [$e, $e / 2];
            })->map(function(int $e) {
                return $e * $e;
            })
            ->sum();

        assert($result === 207708750);
    }

    /**
     * @iterations 1000
     */
    public function flatMapDistinct()
    {
        $iterator = $this->createIntIterator(1000);
        $stream   = Streams::wrap($iterator);
        $result   = $stream
            ->flatMap(function(int $e) {
                return array_fill(0, 10, $e);
            })->distinct()
            ->count();

        assert($result === 1000);
    }
}
