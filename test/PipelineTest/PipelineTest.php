<?php

namespace PipelineTest;

use Iterator;
use ArrayObject;
use ArrayIterator;
use Pipeline\Stream;
use Pipeline\Pipeline;
use Pipeline\Collector;
use Pipeline\Collectors;

class PipelineTest extends BaseStreamTest
{
    protected function createStream(Iterator $source) : Stream
    {
        return Pipeline::head($source);
    }

    public function testMapToNumeric()
    {
        $values   = ['a', 'bb', 'ccc'];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream->mapToNumeric(function(string $e) {
            return strlen($e);
        })->sum();

        $this->assertEquals(6, $result);
    }

    public function testFlatMapToNumeric()
    {
        $values   = [['a', 'bb'], ['ccc', 'dddd']];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = $stream->flatMapToNumeric(function(array $e) {
            return array_map('strlen', $e);
        })->average();

        $this->assertEquals(2.5, $result);
    }

    public function testPeek()
    {
        $values   = ['one', 'two', 'three', 'five'];
        $iterator = new ArrayIterator($values);
        $stream   = $this->createStream($iterator);
        $result   = new ArrayObject();
        $mapping  = [
            'ONE'   => 11,
            'TWO'   => 22,
            'THREE' => 33
        ];

        $sum = $stream
            ->map(function(string $e) {
                return strtoupper($e);
            })
            ->filter(function(string $e) use ($mapping) {
                return isset($mapping[$e]);
            })
            ->mapToNumeric(function(string $e) use ($mapping) {
                return $mapping[$e];
            })
            ->peek(function(int $e) use ($result) {
                $result[] = $e;
            })
            ->filter(function(int $e) {
                return ($e % 2 != 0);
            })
            ->sum();

        $this->assertEquals(44, $sum);
        $this->assertCount(3, $result);
        $this->assertEquals([11, 22, 33], $result->getArrayCopy());
    }

    public function testCountWorks()
    {
        $lines  = $this->readFileLines();
        $result = $this->createStream($lines)
            ->filter(function(string $line) {
                return strlen($line) > 1;
            })
            ->map(function(string $line) {
                return trim(strtolower($line));
            })
            ->flatMap(function(string $line) {
                return explode(' ', $line);
            })
            ->collect(Collectors::groupingBy(function (string $word) {
                return $word;
            }, Collectors::counting()));

        $this->assertCount(100, $result);
        $this->assertEquals(13, $result['the']);
        $this->assertEquals(3, $result['copyright']);
        $this->assertEquals(2, $result['permission']);
    }

    private function readFileLines() : Iterator
    {
        $file = new \SplFileObject(__DIR__ . '/../../LICENSE');

        while ( ! $file->eof()) {
            yield $file->fgets();
        }
    }
}
