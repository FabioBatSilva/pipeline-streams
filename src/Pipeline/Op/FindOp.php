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
 * An operation that searches for an element in a stream pipeline, and terminates when it finds one.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class FindOp extends BaseTerminalOp
{
    /**
     * @var bool
     */
    private $hasValue = false;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var mixed
     */
    private $value;

    /**
     * Construct
     *
     * @param callable $callable
     */
    public function __construct(callable $callable = null)
    {
        $this->callable = $callable;
    }

    /**
     * {@inheritdoc}
     */
    public function begin()
    {
        $this->value    = null;
        $this->hasValue = false;
    }

    /**
     * {@inheritdoc}
     */
    public function accept($item)
    {
        if ($this->callable == null) {
            $this->value    = $item;
            $this->hasValue = true;

            return;
        }

        $callable = $this->callable;
        $result   = $callable($item);

        if ($result === true) {
            $this->value    = $item;
            $this->hasValue = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function cancellationRequested() : bool
    {
        return $this->hasValue;
    }
}
