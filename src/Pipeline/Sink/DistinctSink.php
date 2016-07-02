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
 * A Sink for implementing uniqueness on streams.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class DistinctSink extends ChainSink
{
    /**
     * @var array
     */
    private $values;

    /**
     * Constructor.
     *
     * @param \Pipeline\Sink $downstream
     */
    public function __construct(Sink $downstream)
    {
        $this->downstream = $downstream;
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
        if (in_array($item, $this->values, true)) {
            return;
        }

        $this->values[] = $item;
        $this->downstream->accept($item);
    }
}
