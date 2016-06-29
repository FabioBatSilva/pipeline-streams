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
use Countable;
use RuntimeException;

use Pipeline\Sink;

/**
 * Reference Pipeline
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
abstract class BasePipeline implements Pipeline
{
    /**
     * Backlink to the head of the pipeline chain (self if this is the source stage).
     *
     * @var AbstractPipeline
     */
    protected $sourceStage;

    /**
     * The "upstream" pipeline, or null if this is the source stage.
     *
     * @var AbstractPipeline
     */
    protected $previousStage;

    /**
     * The next stage in the pipeline, or null if this is the last stage.
     * Effectively final at the point of linking to the next pipeline.
     *
     * @var AbstractPipeline
     */
    protected $nextStage;

    /**
     * True if this pipeline has been linked or consumed
     *
     * @var boolean
     */
    protected $consumed = false;

    /**
     * The source spliterator. Only valid for the head pipeline.
     *
     * @var \Iterator
     */
    protected $source;

    /**
     * The combined source and operation flags for the source and all operations
     * up to and including the operation represented by this pipeline object.
     * Valid at the point of pipeline preparation for evaluation.
     *
     * @var int
     */
    protected $combinedFlags = 0;

    /**
     * Construct
     *
     * @param callable $consumer
     * @param boolean  $ordered
     */
    public function __construct(Iterator $source, int $flags = 0, Pipeline $previousStage = null)
    {
        $this->source        = $source;
        $this->combinedFlags = $flags;
        $this->sourceStage   = $this;

        if ($previousStage !== null) {
            $this->combinedFlags += $previousStage->combinedFlags;

            $this->sourceStage   = $previousStage->sourceStage;
            $this->previousStage = $previousStage;

            $previousStage->nextStage = $this;
        }
    }

    /**
     * Returns the exact output size of Iterator
     *
     * @param \Iterator $iterator
     *
     * @return integer the exact size if known, or NULL if infinite or unknown
     */
    public function exactOutputSizeIfKnown(Iterator $iterator)
    {
        if ($iterator instanceof Countable) {
            return $iterator->count();
        }

        return null;
    }

    /**
     * Applies the pipeline stages described by this Pipeline to
     * the provided Iterator and send the results to the provided Sink
     *
     * @param \Pipeline\Sink $sink
     * @param \Iterator      $iterator
     */
    public function wrapAndCopyInto(Sink $sink, Iterator $iterator) : Sink
    {
        $this->copyInto($this->wrapSink($sink), $iterator);

        return $sink;
    }

    /**
     * Pushes elements obtained from the Iterator into the provided Sink
     *
     * @param \Pipeline\Sink $sink
     * @param \Iterator      $iterator
     */
    public function copyInto(Sink $sink, Iterator $iterator)
    {
        $sink->begin($this->exactOutputSizeIfKnown($iterator));

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
            $sink     = $pipeline->opWrapSink($sink, $pipeline->combinedFlags);
            $pipeline = $pipeline->previousStage;
        }

        return $sink;
    }

    /**
     * Evaluate the pipeline with a terminal operation to produce a result.
     *
     * @param <R> the type of result
     * @param terminalOp the terminal operation to be applied to the pipeline.
     * @return the result
     */
    public function evaluate(TerminalOp $terminalOp)
    {
        $flags    = $terminalOp->getOpFlags();
        $iterator = $this->sourceIterator();

        return $terminalOp->evaluate($this, $iterator);
    }

    /**
     * Gets the source stage spliterator if this pipeline stage is the source stage.
     * The pipeline is consumed after this method is called and returns successfully.
     *
     * @return \Iterator
     */
    public function sourceIterator() : Iterator
    {
        if ($this->sourceStage->consumed) {
            throw new RuntimeException('Source already consumed or closed');
        }

        $this->sourceStage->consumed = true;

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
    protected function opWrapSink(Sink $sink, int $flags) : Sink
    {
        return $sink;
    }
}
