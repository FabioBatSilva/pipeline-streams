<?php

namespace PipelineBench;

use Iterator;
use Pipeline\Pipeline;

/**
 * @Warmup(2)
 * @Revs(1000)
 */
class StreamBench
{
    private function createIntIterator(int $size) : Iterator
    {
        for ($i=0; $i < $size; $i++) {
            yield $i;
        }
    }

    public function benchFilter()
    {
        $iterator = $this->createIntIterator(1000);
        $stream   = Pipeline::wrap($iterator);
        $result   = $stream
            ->filter(function(int $e) {
                return $e % 2 == 0;
            })
            ->count();

        assert($result === 500);
    }

    public function benchFilterMap()
    {
        $iterator = $this->createIntIterator(1000);
        $stream   = Pipeline::wrap($iterator);
        $result   = $stream
            ->filter(function(int $e) {
                return $e % 2 == 0;
            })->map(function(int $e) {
                return $e * 10;
            })
            ->count();

        assert($result === 500);
    }

    public function benchFilterMapPeek()
    {
        $iterator = $this->createIntIterator(1000);
        $stream   = Pipeline::wrap($iterator);
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

    public function benchFilterFlatMap()
    {
        $iterator = $this->createIntIterator(1000);
        $stream   = Pipeline::wrap($iterator);
        $result   = $stream
            ->filter(function(int $e) {
                return $e % 2 == 0;
            })->flatMapToInt(function(int $e) {
                return [$e, intval($e / 2)];
            })->map(function(int $e) {
                return $e * $e;
            })
            ->sum();

        assert($result === 207708750);
    }

    public function benchFlatMapDistinct()
    {
        $iterator = $this->createIntIterator(1000);
        $stream   = Pipeline::wrap($iterator);
        $result   = $stream
            ->flatMap(function(int $e) {
                return array_fill(0, 10, $e);
            })
            ->distinct()
            ->count();

        assert($result === 1000);
    }
}
