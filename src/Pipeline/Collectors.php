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

namespace Pipeline;

/**
 * Implementations of Collector that implement various useful reduction operations
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class Collectors
{
    /**
     * Construct
     */
    private function __construct()
    {

    }

    /**
     * Returns a Collector that accumulates the input elements into a new array
     *
     * @return \Pipeline\Collector
     */
    public static function asArray()
    {
        return new class() implements Collector
        {
            private $values = [];

            public function accept($item)
            {
                $this->values[] = $item;
            }

            public function get()
            {
                return $this->values;
            }
        };
    }

    /**
     * Returns a Collector that produces the sum of a numbers
     * function applied to the input elements. If no elements are present, the result is 0.
     *
     * @return \Pipeline\Collector
     */
    public static function summingNumbers()
    {
        return new class() implements Collector
        {
            private $sum = 0;

            public function accept($item)
            {
                $this->sum += $item;
            }

            public function get()
            {
                return $this->sum;
            }
        };
    }

    /**
     * Returns a Collector that produces the arithmetic mean.
     * If no elements are present, the result is 0.
     *
     * @return \Pipeline\Collector
     */
    public static function averagingNumbers()
    {
        return new class() implements Collector
        {
            private $sum   = 0;
            private $count = 0;

            public function accept($item)
            {
                $this->sum += $item;

                $this->count++;
            }

            public function get()
            {
                return $this->count > 0
                    ? $this->sum / $this->count
                    : 0;
            }
        };
    }
}
