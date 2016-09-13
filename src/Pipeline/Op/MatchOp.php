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

/**
 * An operation that evaluates a predicate on the
 * elements of a stream and determines whether all, any or none of those
 * elements match the predicate.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class MatchOp extends BaseTerminalOp
{
    /**
     * Do all elements match the predicate?
     */
    const ANY = 1;

    /**
     * Do any elements match the predicate?
     */
    const ALL = 2;

    /**
     * Do no elements match the predicate?
     */
    const NONE = 3;

    /**
     * @var bool
     */
    private $cancel = false;

    /**
     * @var bool
     */
    private $result;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var int
     */
    private $matchKind;

    /**
     * Construct
     *
     * @param callable $callable
     * @param int      $matchKind
     */
    public function __construct(callable $callable, int $matchKind)
    {
        $this->callable  = $callable;
        $this->matchKind = $matchKind;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->result;
    }

    /**
     * {@inheritdoc}
     */
    public function begin()
    {
        $this->cancel = false;
        $this->result = false;
    }

    /**
     * {@inheritdoc}
     */
    public function accept($item)
    {
        $callable = $this->callable;
        $result   = ($callable($item) === true);

        if (($this->matchKind === self::NONE || $this->matchKind === self::ALL)) {
            $this->result = true;
        }

        if ($this->matchKind === self::ANY && $result) {
            $this->cancel = true;
            $this->result = true;

            return;
        }

        if ($this->matchKind === self::NONE && $result) {
            $this->cancel = true;
            $this->result = false;

            return;
        }

        if ($this->matchKind === self::ALL && ! $result) {
            $this->cancel = true;
            $this->result = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cancellationRequested() : bool
    {
        return $this->cancel;
    }
}
