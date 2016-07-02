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
 * Collector implementing a "group by" operation on input elements.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class GroupByCollector implements Collector
{
    /**
     * @var array
     */
    private $values;

    /**
     * @var callable
     */
    private $classifier;

    /**
     * @param callable $classifier
     */
    public function __construct(callable $classifier)
    {
        $this->classifier = $classifier;
    }

    /**
     * {@inheritdoc}
     */
    public function begin()
    {
        $this->values = [];
    }

    /**
     * {@inheritdoc}
     */
    public function accept($item)
    {
        $callable = $this->classifier;
        $itemKey  = $callable($item);

        $this->values[$itemKey][] = $item;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $result = $this->values;

        $this->values = null;

        return $result;
    }
}
