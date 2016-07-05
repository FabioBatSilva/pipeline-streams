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
 * Collector implementing a "group by" operation on input elements.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class GroupByCollector implements Collector
{
    /**
     * @var callable
     */
    private $classifier;

    /**
     * @var \Pipeline\Collector
     */
    protected $downstream;

    /**
     * Constructor.
     *
     * @param callable            $classifier
     * @param \Pipeline\Collector $downstream
     */
    public function __construct(callable $classifier, Collector $downstream)
    {
        $this->classifier = $classifier;
        $this->downstream = $downstream;
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
        $callable = $this->classifier;
        $itemKey  = $callable($item);

        if ( ! isset($state[$itemKey])) {
            $state[$itemKey] = $this->downstream->begin();
        }

        $this->downstream->accept($state[$itemKey], $item);
    }

    /**
     * {@inheritdoc}
     */
    public function finish($state)
    {
        $values = [];

        foreach ($state as $key => $value) {
            $values[$key] = $this->downstream->finish($value);
        }

        return $values;
    }
}
