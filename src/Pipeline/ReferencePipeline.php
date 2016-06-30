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
use Pipeline\Op\ReduceOp;

use Pipeline\Sink\SortingSink;
use Pipeline\Sink\MappingSink;
use Pipeline\Sink\SlicingSink;
use Pipeline\Sink\InvokingSink;
use Pipeline\Sink\FilteringSink;
use Pipeline\Sink\FlatMappingSink;
use Pipeline\Sink\ChainedReference;

/**
 * Reference Pipeline
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class ReferencePipeline extends BasePipeline
{
    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate) : Pipeline
    {
        $source = $this->source;
        $flags  = $this->combinedFlags;

        return new class($source, $flags, $this, $predicate) extends ReferencePipeline
        {
            private $callable;

            public function __construct($source, $flags, $self, $callable)
            {
                parent::__construct($source, $flags, $self);

                $this->callable = $callable;
            }

            protected function opWrapSink(Sink $sink, int $flags) : Sink
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
        $source = $this->source;
        $flags  = $this->combinedFlags;

        return new class($source, $flags, $this, $mapper) extends ReferencePipeline
        {
            private $callable;

            public function __construct($source, $flags, $self, $callable)
            {
                parent::__construct($source, $flags, $self);

                $this->callable = $callable;
            }

            protected function opWrapSink(Sink $sink, int $flags) : Sink
            {
                return new MappingSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function mapToInt(callable $mapper) : IntPipeline
    {

    }

    /**
     * {@inheritdoc}
     */
    public function flatMap(callable $mapper) : Pipeline
    {
        $source = $this->source;
        $flags  = $this->combinedFlags;

        return new class($source, $flags, $this, $mapper) extends ReferencePipeline
        {
            private $callable;

            public function __construct($source, $flags, $self, $callable)
            {
                parent::__construct($source, $flags, $self);

                $this->callable = $callable;
            }

            protected function opWrapSink(Sink $sink, int $flags) : Sink
            {
                return new FlatMappingSink($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function flatMapToInt(callable $mapper) : IntPipeline
    {

    }

    /**
     * {@inheritdoc}
     */
    public function distinct() : Pipeline
    {

    }

    /**
     * {@inheritdoc}
     */
    public function sorted(callable $comparator = null) : Pipeline
    {
        $source = $this->source;
        $flags  = $this->combinedFlags;

        return new class($source, $flags, $this, $comparator) extends ReferencePipeline
        {
            private $callable;

            public function __construct($source, $flags, $self, $callable)
            {
                parent::__construct($source, $flags, $self);

                $this->callable = $callable;
            }

            protected function opWrapSink(Sink $sink, int $flags) : Sink
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
        $source = $this->source;
        $flags  = $this->combinedFlags;

        return new class($source, $flags, $this, $action) extends ReferencePipeline
        {
            private $callable;

            public function __construct($source, $flags, $self, $callable)
            {
                parent::__construct($source, $flags, $self);

                $this->callable = $callable;
            }

            protected function opWrapSink(Sink $sink, int $flags) : Sink
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
        $source = $this->source;
        $flags  = $this->combinedFlags;

        return new class($source, $flags, $this, $maxSize) extends ReferencePipeline
        {
            private $maxSize;

            public function __construct($source, $flags, $self, $maxSize)
            {
                parent::__construct($source, $flags, $self);

                $this->maxSize = $maxSize;
            }

            protected function opWrapSink(Sink $sink, int $flags) : Sink
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
        $source = $this->source;
        $flags  = $this->combinedFlags;

        return new class($source, $flags, $this, $skip) extends ReferencePipeline
        {
            private $skip;

            public function __construct($source, $flags, $self, $skip)
            {
                parent::__construct($source, $flags, $self);

                $this->skip = $skip;
            }

            protected function opWrapSink(Sink $sink, int $flags) : Sink
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
        $identity    = new ArrayObject();
        $accumulator = function ($item, ArrayObject $state) {

            $state->append($item);

            return $state;
        };

        $result = $this->evaluate(new ReduceOp($accumulator, $identity));
        $array  = $result->getArrayCopy();

        return $array;
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
    public function collect(callable $collector)
    {

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
    public function anyMatch(callable $predicate) : boolean
    {

    }

    /**
     * {@inheritdoc}
     */
    public function allMatch(callable $predicate) : boolean
    {

    }

    /**
     * {@inheritdoc}
     */
    public function noneMatch(callable $predicate) : boolean
    {

    }

    /**
     * {@inheritdoc}
     */
    public function findFirst()
    {

    }
}
