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
 * Collector map each element end push to a downstream collector
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class MapCollector implements Collector
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * @var \Pipeline\Collector
     */
    protected $downstream;

    /**
     * Constructor.
     *
     * @param callable            $callable
     * @param \Pipeline\Collector $downstream
     */
    public function __construct(callable $callable, Collector $downstream)
    {
        $this->callable   = $callable;
        $this->downstream = $downstream;
    }

    /**
     * {@inheritdoc}
     */
    public function begin()
    {
        return $this->downstream->begin();
    }

    /**
     * {@inheritdoc}
     */
    public function accept($state, $item)
    {
        $callable = $this->callable;
        $result   = $callable($item);

        $this->downstream->accept($state, $result);
    }

    /**
     * {@inheritdoc}
     */
    public function finish($state)
    {
        return $this->downstream->finish($state);
    }
}
