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

/**
 * An operation in a stream pipeline that implement reductions.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class ReduceOp extends BaseTerminalOp
{
    /**
     * @var mixed
     */
    private $state;

    /**
     * @var mixed
     */
    private $identity;

    /**
     * @var callable
     */
    private $accumulator;

    /**
     * Construct
     *
     * @param callable $accumulator
     * @param mixed    $identity
     */
    public function __construct(callable $accumulator, $identity = null)
    {
        $this->identity    = $identity;
        $this->accumulator = $accumulator;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function begin(int $size = null)
    {
        $this->state = $this->identity;
    }

    /**
     * {@inheritdoc}
     */
    public function accept($item)
    {
        $callable = $this->accumulator;
        $state    = $callable($item, $this->state);

        $this->state = $state;
    }
}
