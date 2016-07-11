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

use ArrayObject;
use Pipeline\Collector;

/**
 * Collector that accumulates elements into a
 * associative array whose keys and values are the result of applying
 * the provided mapping functions to the input elements.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class ArrayMapCollector implements Collector
{
    /**
     * @var callable
     */
    private $keyMapper;

    /**
     * @var callable
     */
    private $valueMapper;

    /**
     * @var callable
     */
    private $mergeFunction;

    /**
     * Constructor.
     *
     * @param callable $keyMapper
     * @param callable $valueMapper
     * @param callable $mergeFunction
     */
    public function __construct(callable $keyMapper, callable $valueMapper, callable $mergeFunction)
    {
        $this->keyMapper     = $keyMapper;
        $this->valueMapper   = $valueMapper;
        $this->mergeFunction = $mergeFunction;
    }

    /**
     * {@inheritdoc}
     */
    public function begin()
    {
        return new ArrayObject();
    }

    /**
     * {@inheritdoc}
     */
    public function accept($state, $item)
    {
        $keyMapper   = $this->keyMapper;
        $valueMapper = $this->valueMapper;

        $itemKey   = $keyMapper($item);
        $itemValue = $valueMapper($item);

        if ( ! isset($state[$itemKey])) {
            $state[$itemKey] = $itemValue;

            return;
        }

        $mergeFunction = $this->mergeFunction;
        $mergeValue    = $mergeFunction($state[$itemKey], $itemValue, $itemKey);

        $state[$itemKey] = $mergeValue;
    }

    /**
     * {@inheritdoc}
     */
    public function finish($state)
    {
        return $state->getArrayCopy();
    }
}
