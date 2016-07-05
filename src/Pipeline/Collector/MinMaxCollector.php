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

namespace Pipeline\Collector;

use Pipeline\Collector;

/**
 * Collector that produces the minimal/maximal element according to a given comparator.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class MinMaxCollector implements Collector
{
    /**
     * produces the minimal element
     */
    const MIN = 1;

    /**
     * produces the maximal element
     */
    const MAX = -1;

    /**
     * @var int
     */
    private $type;

    /**
     * Construct
     *
     * @param callable $comparator
     * @param int      $type
     */
    public function __construct(callable $comparator, int $type)
    {
        $this->comparator = $comparator;
        $this->type       = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function begin()
    {
        return (object) [
            'value'    => null,
            'hasValue' => false
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function accept($state, $item)
    {
        if ( ! $state->hasValue) {
            $state->value    = $item;
            $state->hasValue = true;

            return;
        }

        $comparator = $this->comparator;
        $result     = $comparator($state->value, $item);

        if ($result === $this->type) {
            $state->value = $item;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finish($state)
    {
        return $state->value;
    }
}
