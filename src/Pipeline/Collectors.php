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

use Pipeline\Collector\SumCollector;
use Pipeline\Collector\ArrayCollector;
use Pipeline\Collector\MinMaxCollector;
use Pipeline\Collector\AverageCollector;
use Pipeline\Collector\GroupByCollector;

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
        return new ArrayCollector();
    }

    /**
     * Returns a Collector that produces the sum of a numbers
     * function applied to the input elements. If no elements are present, the result is 0.
     *
     * @return \Pipeline\Collector
     */
    public static function summingNumbers()
    {
        return new SumCollector();
    }

    /**
     * Returns a Collector that produces the arithmetic mean.
     * If no elements are present, the result is 0.
     *
     * @return \Pipeline\Collector
     */
    public static function averagingNumbers()
    {
        return new AverageCollector();
    }

    /**
     * Returns a Collector that produces the minimal element according to a given comparator.
     *
     * @param callable $comparator
     *
     * @return \Pipeline\Collector
     */
    public static function minBy(callable $comparator)
    {
        return new MinMaxCollector($comparator, MinMaxCollector::MIN);
    }

    /**
     * Returns a Collector that produces the maximal element according to a given comparator.
     *
     * @param callable $comparator
     *
     * @return \Pipeline\Collector
     */
    public static function maxBy(callable $comparator)
    {
        return new MinMaxCollector($comparator, MinMaxCollector::MAX);
    }

    /**
     * Returns a Collector implementing a "group by" operation on input elements,
     * grouping elements according to a classification function, and returning the results in a array.
     *
     * @param callable $comparator
     *
     * @return \Pipeline\Collector
     */
    public static function groupingBy(callable $classifier)
    {
        return new GroupByCollector($classifier);
    }
}
