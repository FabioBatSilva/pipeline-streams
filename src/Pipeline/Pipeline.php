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

declare(strict_types=1);

namespace Pipeline;

use Iterator;

use Pipeline\Sink\MapSink;
use Pipeline\Sink\SortSink;
use Pipeline\Sink\SliceSink;
use Pipeline\Sink\InvokeSink;
use Pipeline\Sink\FilterSink;
use Pipeline\Sink\FlatMapSink;
use Pipeline\Sink\DistinctSink;

/**
 * Implements a stream stage or stream source stage implementing whose elements are of any type.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class Pipeline extends BaseStream implements MixedStream
{
    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate) : Stream
    {
        return $this->createMixedStream($this, function(Sink $sink) use ($predicate) {
            return new FilterSink($sink, $predicate);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $mapper) : Stream
    {
        return $this->createMixedStream($this, function(Sink $sink) use ($mapper) {
            return new MapSink($sink, $mapper);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function mapToNumeric(callable $mapper) : NumericStream
    {
        return $this->createNumericStream($this, function(Sink $sink) use ($mapper) {
            return new MapSink($sink, $mapper);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function flatMap(callable $mapper) : Stream
    {
        return $this->createMixedStream($this, function(Sink $sink) use ($mapper) {
            return new FlatMapSink($sink, $mapper);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function flatMapToNumeric(callable $mapper) : NumericStream
    {
        return $this->createNumericStream($this, function(Sink $sink) use ($mapper) {
            return new FlatMapSink($sink, $mapper);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function distinct() : Stream
    {
        return $this->createMixedStream($this, function(Sink $sink) {
            return new DistinctSink($sink);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function sorted(callable $comparator = null) : Stream
    {
        return $this->createMixedStream($this, function(Sink $sink) use ($comparator) {
            return new SortSink($sink, $comparator);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function peek(callable $action) : Stream
    {
        return $this->createMixedStream($this, function(Sink $sink) use ($action) {
            return new InvokeSink($sink, $action);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function limit(int $maxSize) : Stream
    {
        return $this->createMixedStream($this, function(Sink $sink) use ($maxSize) {
            return new SliceSink($sink, null, $maxSize);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function skip(int $skip) : Stream
    {
        return $this->createMixedStream($this, function(Sink $sink) use ($skip) {
            return new SliceSink($sink, $skip, null);
        });
    }
}
