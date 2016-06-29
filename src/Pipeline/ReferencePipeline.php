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

use Pipeline\Op\ForEachOp;
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
        return new class($this->source, $this->combinedFlags, $this, $predicate) extends ReferencePipeline
        {
            private $predicate;

            public function __construct($source, $flags, $previousStage, $predicate)
            {
                parent::__construct($source, $flags, $previousStage);

                $this->predicate = $predicate;
            }

            protected function opWrapSink(Sink $sink, int $flags) : Sink
            {
                return new class ($sink, $this->predicate) extends ChainedReference
                {
                    private $predicate;

                    public function __construct($sink, $predicate)
                    {
                        parent::__construct($sink);

                        $this->predicate = $predicate;
                    }

                    public function accept($item)
                    {
                        if (call_user_func($this->predicate, $item)) {
                            parent::accept($item);
                        }
                    }
                };
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $mapper) : Pipeline
    {
        return new class($this->source, $this->combinedFlags, $this, $mapper) extends ReferencePipeline
        {
            private $mapper;

            public function __construct($source, $flags, $previousStage, $mapper)
            {
                parent::__construct($source, $flags, $previousStage);

                $this->mapper = $mapper;
            }

            protected function opWrapSink(Sink $sink, int $flags) : Sink
            {
                return new class ($sink, $this->mapper) extends ChainedReference
                {
                    private $mapper;

                    public function __construct($sink, $mapper)
                    {
                        parent::__construct($sink);

                        $this->mapper = $mapper;
                    }

                    public function accept($item)
                    {
                        parent::accept(call_user_func($this->mapper, $item));
                    }
                };
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
        return new class($this->source, $this->combinedFlags, $this, $action) extends ReferencePipeline
        {
            private $action;

            public function __construct($source, $flags, $previousStage, $action)
            {
                parent::__construct($source, $flags, $previousStage);

                $this->action = $action;
            }

            protected function opWrapSink(Sink $sink, int $flags) : Sink
            {
                return new class ($sink, $this->action) extends ChainedReference
                {
                    private $action;

                    public function __construct($sink, $action)
                    {
                        parent::__construct($sink);

                        $this->action = $action;
                    }

                    public function accept($item)
                    {
                        call_user_func($this->action, $item);
                        parent::accept($item);
                    }
                };
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

    }

    /**
     * {@inheritdoc}
     */
    public function reduce(callable $accumulator, $identity = null) : mixed
    {

    }

    /**
     * {@inheritdoc}
     */
    public function collect(callable $collector) : mixed
    {

    }

    /**
     * {@inheritdoc}
     */
    public function min(callable $comparator) : mixed
    {

    }

    /**
     * {@inheritdoc}
     */
    public function max(callable $comparator) : mixed
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
    public function findFirst() : mixed
    {

    }
}
