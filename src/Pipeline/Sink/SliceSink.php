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

namespace Pipeline\Sink;

use Pipeline\Sink;

/**
 * A Sink for slicing a stream.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class SliceSink extends ChainSink
{
    /**
     * @var callable
     */
    private $skip;

    /**
     * @var callable
     */
    private $limit;

    /**
     * @var integer
     */
    private $offset = 0;

    /**
     * Constructor.
     *
     * @param \Pipeline\Sink $downstream
     * @param int            $skip
     * @param int            $limit
     */
    public function __construct(Sink $downstream, int $skip = null, int $limit = null)
    {
        $this->downstream = $downstream;
        $this->limit      = $limit;
        $this->skip       = $skip;
    }

    /**
     * {@inheritdoc}
     */
    public function begin()
    {
        $this->offset = 0;

        $this->downstream->begin();
    }

    /**
     * {@inheritdoc}
     */
    public function accept($item)
    {
        $this->offset ++;

        if ($this->skip !== null && $this->offset <= $this->skip) {
            return;
        }

        if ($this->limit !== null && $this->offset > $this->limit) {
            return;
        }

        $this->downstream->accept($item);
    }

    /**
     * {@inheritdoc}
     */
    public function cancellationRequested() : bool
    {
        if ($this->limit !== null && $this->offset >= $this->limit) {
            return true;
        }

        return $this->downstream->cancellationRequested();
    }
}
