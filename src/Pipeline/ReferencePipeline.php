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

use Pipeline\Op\FindOp;
use Pipeline\Op\MatchOp;
use Pipeline\Op\ReduceOp;
use Pipeline\Op\CollectOp;
use Pipeline\Op\ForEachOp;

use Pipeline\Sink\MapSink;
use Pipeline\Sink\SortSink;
use Pipeline\Sink\SliceSink;
use Pipeline\Sink\InvokeSink;
use Pipeline\Sink\FilterSink;
use Pipeline\Sink\FlatMapSink;
use Pipeline\Sink\DistinctSink;

/**
 * Implements a pipeline stage or pipeline source stage implementing whose elements are of any type.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class ReferencePipeline extends BaseStream
{
    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate) : Stream
    {
        return new class($this, $predicate) extends ReferencePipeline
        {
            private $callable;

            public function __construct($self, $callable)
            {
                $this->sourceStage   = $self->sourceStage;
                $this->callable      = $callable;
                $this->previousStage = $self;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new FilterSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $mapper) : Stream
    {
        return new class($this, $mapper) extends ReferencePipeline
        {
            private $callable;

            public function __construct($self, $callable)
            {
                $this->sourceStage   = $self->sourceStage;
                $this->callable      = $callable;
                $this->previousStage = $self;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new MapSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function mapToNumeric(callable $mapper) : NumericStream
    {
        return new class($this, $mapper) extends NumericPipeline
        {
            private $callable;

            public function __construct($self, $callable)
            {
                $this->sourceStage   = $self->sourceStage;
                $this->callable      = $callable;
                $this->previousStage = $self;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new MapSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function flatMap(callable $mapper) : Stream
    {
        return new class($this, $mapper) extends ReferencePipeline
        {
            private $callable;

            public function __construct($self, $callable)
            {
                $this->sourceStage   = $self->sourceStage;
                $this->callable      = $callable;
                $this->previousStage = $self;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new FlatMapSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function flatMapToNumeric(callable $mapper) : NumericStream
    {
        return new class($this, $mapper) extends NumericPipeline
        {
            private $callable;

            public function __construct($self, $callable)
            {
                $this->sourceStage   = $self->sourceStage;
                $this->callable      = $callable;
                $this->previousStage = $self;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new FlatMapSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function distinct() : Stream
    {
        return new class($this) extends ReferencePipeline
        {
            public function __construct($self)
            {
                $this->sourceStage   = $self->sourceStage;
                $this->previousStage = $self;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new DistinctSink($sink);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function sorted(callable $comparator = null) : Stream
    {
        return new class($this, $comparator) extends ReferencePipeline
        {
            private $callable;

            public function __construct($self, $callable)
            {
                $this->sourceStage   = $self->sourceStage;
                $this->callable      = $callable;
                $this->previousStage = $self;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new SortSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function peek(callable $action) : Stream
    {
        return new class($this, $action) extends ReferencePipeline
        {
            private $callable;

            public function __construct($self, $callable)
            {
                $this->sourceStage   = $self->sourceStage;
                $this->callable      = $callable;
                $this->previousStage = $self;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new InvokeSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function limit(int $maxSize) : Stream
    {
        return new class($this, $maxSize) extends ReferencePipeline
        {
            private $maxSize;

            public function __construct($self, $maxSize)
            {
                $this->sourceStage   = $self->sourceStage;
                $this->maxSize       = $maxSize;
                $this->previousStage = $self;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new SliceSink($sink, null, $this->maxSize);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function skip(int $skip) : Stream
    {
        return new class($this, $skip) extends ReferencePipeline
        {
            private $skip;

            public function __construct($self, $skip)
            {
                $this->sourceStage   = $self->sourceStage;
                $this->previousStage = $self;
                $this->skip          = $skip;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new SliceSink($sink, $this->skip, null);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $action)
    {
        $this->evaluate(new ForEachOp($action, false));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return $this->collect(Collectors::asArray());
    }

    /**
     * {@inheritdoc}
     */
    public function reduce(callable $accumulator, $identity = null)
    {
        return $this->evaluate(new ReduceOp($accumulator, $identity));
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Collector $collector)
    {
        return $this->evaluate(new CollectOp($collector));
    }

    /**
     * {@inheritdoc}
     */
    public function min(callable $comparator = null)
    {
        if ($comparator === null) {
            $comparator = Collectors::defaultComparator();
        }

        return $this->collect(Collectors::minBy($comparator));
    }

    /**
     * {@inheritdoc}
     */
    public function max(callable $comparator = null)
    {
        if ($comparator === null) {
            $comparator = Collectors::defaultComparator();
        }

        return $this->collect(Collectors::maxBy($comparator));
    }

    /**
     * {@inheritdoc}
     */
    public function count() : int
    {
        $count    = 0;
        $callable = function ($_, int $state) {
            return ++ $state;
        };

        return $this->evaluate(new ReduceOp($callable, $count));
    }

    /**
     * {@inheritdoc}
     */
    public function anyMatch(callable $predicate) : bool
    {
        return $this->evaluate(new MatchOp($predicate, MatchOp::ANY));
    }

    /**
     * {@inheritdoc}
     */
    public function allMatch(callable $predicate) : bool
    {
        return $this->evaluate(new MatchOp($predicate, MatchOp::ALL));
    }

    /**
     * {@inheritdoc}
     */
    public function noneMatch(callable $predicate) : bool
    {
        return $this->evaluate(new MatchOp($predicate, MatchOp::NONE));
    }

    /**
     * {@inheritdoc}
     */
    public function findFirst(callable $predicate = null)
    {
        return $this->evaluate(new FindOp($predicate));
    }
}
