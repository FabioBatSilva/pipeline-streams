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

use ArrayObject;

use Pipeline\Op\ForEachOp;
use Pipeline\Op\CollectOp;
use Pipeline\Op\ReduceOp;
use Pipeline\Op\MatchOp;
use Pipeline\Op\FindOp;

use Pipeline\Sink\SortingSink;
use Pipeline\Sink\MappingSink;
use Pipeline\Sink\SlicingSink;
use Pipeline\Sink\InvokingSink;
use Pipeline\Sink\FilteringSink;
use Pipeline\Sink\FlatMappingSink;
use Pipeline\Sink\DistinguishSink;

/**
 * Default Pipeline
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class DefaultPipeline extends BasePipeline
{
    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate) : Pipeline
    {
        return new class($this, $predicate) extends DefaultPipeline
        {
            private $callable;

            public function __construct($source, $callable)
            {
                parent::__construct($source);

                $this->callable = $callable;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new FilteringSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $mapper) : Pipeline
    {
        return new class($this, $mapper) extends DefaultPipeline
        {
            private $callable;

            public function __construct($source, $callable)
            {
                parent::__construct($source);

                $this->callable = $callable;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new MappingSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function mapToNumeric(callable $mapper) : NumericPipeline
    {
        return new class($this, $mapper) extends DefaultNumericPipeline
        {
            private $callable;

            public function __construct($source, $callable)
            {
                parent::__construct($source);

                $this->callable = $callable;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new MappingSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function flatMap(callable $mapper) : Pipeline
    {
        return new class($this, $mapper) extends DefaultPipeline
        {
            private $callable;

            public function __construct($source, $callable)
            {
                parent::__construct($source);

                $this->callable = $callable;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new FlatMappingSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function flatMapToNumeric(callable $mapper) : NumericPipeline
    {
        return new class($this, $mapper) extends DefaultNumericPipeline
        {
            private $callable;

            public function __construct($source, $callable)
            {
                parent::__construct($source);

                $this->callable = $callable;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new FlatMappingSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function distinct() : Pipeline
    {
        return new class($this) extends DefaultPipeline
        {
            protected function opWrapSink(Sink $sink) : Sink
            {
                return new DistinguishSink($sink);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function sorted(callable $comparator = null) : Pipeline
    {
        return new class($this, $comparator) extends DefaultPipeline
        {
            private $callable;

            public function __construct($source, $callable)
            {
                parent::__construct($source);

                $this->callable = $callable;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new SortingSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function peek(callable $action) : Pipeline
    {
        return new class($this, $action) extends DefaultPipeline
        {
            private $callable;

            public function __construct($source, $callable)
            {
                parent::__construct($source);

                $this->callable = $callable;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new InvokingSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function limit(int $maxSize) : Pipeline
    {
        return new class($this, $maxSize) extends DefaultPipeline
        {
            private $maxSize;

            public function __construct($source, $maxSize)
            {
                parent::__construct($source);

                $this->maxSize = $maxSize;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new SlicingSink($sink, null, $this->maxSize);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function skip(int $skip) : Pipeline
    {
        return new class($this, $skip) extends DefaultPipeline
        {
            private $skip;

            public function __construct($source, $skip)
            {
                parent::__construct($source);

                $this->skip = $skip;
            }

            protected function opWrapSink(Sink $sink) : Sink
            {
                return new SlicingSink($sink, $this->skip, null);
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
            $comparator = function ($item, $current) {

                if ($current === null){
                    return $item;
                }

                if ($item < $current){
                    return $item;
                }

                return $current;
            };
        }

        return $this->evaluate(new ReduceOp($comparator));
    }

    /**
     * {@inheritdoc}
     */
    public function max(callable $comparator = null)
    {
        if ($comparator === null) {
            $comparator = function ($item, $current) {

                if ($current === null){
                    return $item;
                }

                if ($item > $current){
                    return $item;
                }

                return $current;
            };
        }

        return $this->evaluate(new ReduceOp($comparator));
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
