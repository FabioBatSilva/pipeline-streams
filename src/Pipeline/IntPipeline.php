<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types = 1);

namespace Pipeline;

use ArrayIterator;
use Traversable;

use Pipeline\Sink\DistinctSink;
use Pipeline\Sink\FilterSink;
use Pipeline\Sink\FlatMapSink;
use Pipeline\Sink\InvokeSink;
use Pipeline\Sink\MapSink;
use Pipeline\Sink\SliceSink;
use Pipeline\Sink\SortSink;

/**
 * Implements a pipeline stage or pipeline source stage implementing whose elements are integers.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class IntPipeline extends BaseStream implements IntStream
{
    /**
     * Returns a FloatStream containing the parameters as elements
     *
     * @param int $values,...
     *
     * @return \Pipeline\IntPipeline
     */
    public static function of(int ...$values) : IntPipeline
    {
        $iterator = new ArrayIterator($values);
        $pipeline = new IntPipeline($iterator);

        return $pipeline;
    }

    /**
     * Wrap the input values in a FloatStream.
     *
     * @param array|\Traversable $source
     *
     * @return \Pipeline\IntPipeline
     *
     * @throws \InvalidArgumentException if the $source arg is not valid.
     */
    public static function wrap($source) : IntPipeline
    {
        if ($source instanceof Traversable) {
            return new IntPipeline($source);
        }

        if (is_array($source)) {
            return self::of(...array_values($source));
        }

        throw new \InvalidArgumentException(sprintf(
            'Argument 1 passed to %s($source) must be an instance of array|\Traversable, %s given.',
            __METHOD__,
            is_object($source) ? get_class($source) : gettype($source)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function average() : int
    {
        return (int) $this->collect(Collectors::averagingNumbers());
    }

    /**
     * {@inheritdoc}
     */
    public function sum() : int
    {
        return (int) $this->collect(Collectors::summingNumbers());
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate) : Stream
    {
        return new IntPipelineStage($this, function(Sink $sink) use ($predicate) {
            return new FilterSink($sink, $predicate);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $mapper) : Stream
    {
        return new IntPipelineStage($this, function(Sink $sink) use ($mapper) {
            return new MapSink($sink, $mapper);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function mapToFloat(callable $mapper) : FloatStream
    {
        return new FloatPipelineStage($this, function(Sink $sink) use ($mapper) {
            return new MapSink($sink, $mapper);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function mapToMixed(callable $mapper) : MixedStream
    {
        return new PipelineStage($this, function(Sink $sink) use ($mapper) {
            return new MapSink($sink, $mapper);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function flatMap(callable $mapper) : Stream
    {
        return new IntPipelineStage($this, function(Sink $sink) use ($mapper) {
            return new FlatMapSink($sink, $mapper);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function flatMapToFloat(callable $mapper) : FloatStream
    {
        return new FloatPipelineStage($this, function(Sink $sink) use ($mapper) {
            return new FlatMapSink($sink, $mapper);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function flatMapToMixed(callable $mapper) : MixedStream
    {
        return new PipelineStage($this, function(Sink $sink) use ($mapper) {
            return new FlatMapSink($sink, $mapper);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function distinct() : Stream
    {
        return new IntPipelineStage($this, function(Sink $sink) {
            return new DistinctSink($sink);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function sorted(callable $comparator = null) : Stream
    {
        return new IntPipelineStage($this, function(Sink $sink) use ($comparator) {
            return new SortSink($sink, $comparator);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function peek(callable $action) : Stream
    {
        return new IntPipelineStage($this, function(Sink $sink) use ($action) {
            return new InvokeSink($sink, $action);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function limit(int $maxSize) : Stream
    {
        return new IntPipelineStage($this, function(Sink $sink) use ($maxSize) {
            return new SliceSink($sink, null, $maxSize);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function skip(int $skip) : Stream
    {
        return new IntPipelineStage($this, function(Sink $sink) use ($skip) {
            return new SliceSink($sink, $skip, null);
        });
    }
}
