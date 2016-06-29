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

namespace Pipeline\Op;

use Iterator;

use Pipeline\BaseSink;
use Pipeline\TerminalOp;
use Pipeline\TerminalSink;
use Pipeline\BasePipeline;
use Pipeline\PipelineOpFlag;

/**
 * An operation in a stream pipeline that takes a stream as input and produces a result or side-effect.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class ForEachOp extends BaseSink implements TerminalOp, TerminalSink
{
    private $consumer;

    private $ordered;

    /**
     * Construct
     *
     * @param callable $consumer
     * @param boolean  $ordered
     */
    public function __construct(callable $consumer, bool $ordered)
    {
        $this->ordered  = $ordered;
        $this->consumer = $consumer;
    }

    /**
     * {@inheritdoc}
     */
    public function getOpFlags() : int
    {
        return $this->ordered ? 0 : PipelineOpFlag::NOT_ORDERED;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate(BasePipeline $pipeline, Iterator $iterator)
    {
        return $pipeline->wrapAndCopyInto($this, $iterator)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function accept($t)
    {
        call_user_func($this->consumer, $t);
    }
}
