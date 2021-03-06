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

declare(strict_types = 1);

namespace Pipeline\Op;

use Pipeline\Collector;

/**
 * An operation in a stream pipeline that collect values.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class CollectOp extends BaseTerminalOp
{
    /**
     * @var \Pipeline\Collector
     */
    private $collector;

    /**
     * @var mixed
     */
    private $state;

    /**
     * Construct
     *
     * @param Collector $collector
     */
    public function __construct(Collector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * {@inheritdoc}
     */
    public function begin()
    {
        $collector = $this->collector;
        $state     = $collector->begin();

        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function accept($item)
    {
        $this->collector->accept($this->state, $item);
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $collector = $this->collector;
        $state     = $collector->finish($this->state);

        $this->state = null;

        return $state;
    }
}
