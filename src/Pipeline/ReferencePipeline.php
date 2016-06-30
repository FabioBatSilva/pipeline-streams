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

use Pipeline\Sink\FilterWrapper;
use Pipeline\Sink\ActionWrapper;
use Pipeline\Sink\MapperWrapper;
use Pipeline\Sink\ChainedReference;
use Pipeline\Sink\FlatMapperWrapper;

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
                return new FilterWrapper($sink, $this->callable);
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
                return new MapperWrapper($sink, $this->callable);
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
                return new FlatMapperWrapper($sink, $this->callable);
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
    public function sorted(callable $comparator) : Pipeline
    {

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
                return new ActionWrapper($sink, $this->callable);
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function limit(integer $maxSize) : Pipeline
    {

    }

    /**
     * {@inheritdoc}
     */
    public function skip(integer $n) : Pipeline
    {

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
    public function min(callable $comparator)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function max(callable $comparator)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function count() : integer
    {

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
