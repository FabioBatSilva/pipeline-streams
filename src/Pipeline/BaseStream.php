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

use Traversable;
use RuntimeException;

use Pipeline\Sink;
use Pipeline\Op\FindOp;
use Pipeline\Op\MatchOp;
use Pipeline\Op\ReduceOp;
use Pipeline\Op\CollectOp;
use Pipeline\Op\ForEachOp;

/**
 * Base Stream Pipeline implementation
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
abstract class BaseStream implements Stream
{
    /**
     * The "upstream" pipeline, or null if this is the source stage.
     *
     * @var BaseStream
     */
    protected $previousStage;

    /**
     * Backlink to the head of the pipeline chain (self if this is the source stage).
     *
     * @var BaseStream
     */
    protected $sourceStage;

    /**
     * The source spliterator. Only valid for the head pipeline.
     *
     * @var \Traversable
     */
    protected $source;

    /**
     * Construct
     *
     * @param \Traversable $source
     */
    public function __construct(Traversable $source)
    {
        $this->source      = $source;
        $this->sourceStage = $this;
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(callable $action)
    {
        $this->evaluate(new ForEachOp($action));
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
    public function reduce($state, callable $accumulator)
    {
        return $this->evaluate(new ReduceOp($accumulator, $state));
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
            $comparator = self::defaultComparator();
        }

        return $this->collect(Collectors::minBy($comparator));
    }

    /**
     * {@inheritdoc}
     */
    public function max(callable $comparator = null)
    {
        if ($comparator === null) {
            $comparator = self::defaultComparator();
        }

        return $this->collect(Collectors::maxBy($comparator));
    }

    /**
     * {@inheritdoc}
     */
    public function count() : int
    {
        return $this->collect(Collectors::counting());
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

    /**
     * Applies the pipeline stages described by this Pipeline to
     * the provided Traversable and send the results to the provided Sink
     *
     * @param \Pipeline\Sink $sink
     * @param \Traversable      $iterator
     */
    public function wrapAndCopyInto(Sink $sink, Traversable $iterator) : Sink
    {
        $this->copyInto($this->wrapSink($sink), $iterator);

        return $sink;
    }

    /**
     * Pushes elements obtained from the Traversable into the provided Sink
     *
     * @param \Pipeline\Sink $sink
     * @param \Traversable      $iterator
     */
    public function copyInto(Sink $sink, Traversable $iterator)
    {
        $sink->begin();

        foreach ($iterator as $value) {
            $sink->accept($value);

            if ($sink->cancellationRequested()) {
                break;
            }
        }

        $sink->end();
    }

    /**
     * Takes a Sink that accepts elements of the output type of the Pipeline,
     * and wrap it with a Sink that accepts
     * elements of the input type and implements all the intermediate operations
     * described by this Pipeline, delivering the result into the provided Sink.
     *
     * @param \Pipeline\Sink $sink
     *
     * @return \Pipeline\Sink
     */
    public function wrapSink(Sink $sink) : Sink
    {
        $pipeline = $this;

        while ($pipeline !== null) {
            $sink     = $pipeline->opWrapSink($sink);
            $pipeline = $pipeline->previousStage;
        }

        return $sink;
    }

    /**
     * Evaluate the pipeline with a terminal operation to produce a result.
     *
     * @param TerminalOp $terminalOp the terminal operation to be applied to the pipeline.
     *
     * @return mixed
     */
    public function evaluate(TerminalOp $terminalOp)
    {
        $iterator = $this->sourceTraversable();
        $result   = $terminalOp->evaluate($this, $iterator);

        return $result;
    }

    /**
     * Gets the source stage spliterator if this pipeline stage is the source stage.
     * The pipeline is consumed after this method is called and returns successfully.
     *
     * @return \Traversable
     */
    protected function sourceTraversable() : Traversable
    {
        return $this->sourceStage->source;
    }

    /**
     * Accepts a Sink which will receive the results of this operation,
     * and return a Sink which accepts elements of the input type of
     * this operation and which performs the operation, passing the results to
     * the provided Sink.
     *
     * @param \Pipeline\Sink $sink
     *
     * @return \Pipeline\Sink
     */
    protected function opWrapSink(Sink $sink) : Sink
    {
        return $sink;
    }

    /**
     * Returns a callable comparator.
     *
     * @return callable
     */
    private static function defaultComparator() : callable
    {
        return function ($a, $b) {
            if ($a === $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        };
    }
}
