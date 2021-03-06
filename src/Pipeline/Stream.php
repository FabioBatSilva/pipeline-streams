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

namespace Pipeline;

/**
 * A sequence of elements supporting pipeline operations.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
interface Stream
{
    /**
     * Returns a stream consisting of the elements of this stream that match the given predicate.
     *
     * @param callable $predicate Predicate to apply to each element to determine if it should be included
     *
     * @return \Pipeline\Stream
     */
    public function filter(callable $predicate) : Stream;

    /**
     * Returns a stream consisting of the results of applying the
     * given function to the elements of this stream.
     *
     * @param callable $mapper callable to apply to each element
     *
     * @return \Pipeline\Stream
     */
    public function map(callable $mapper) : Stream;

    /**
     * Returns a stream consisting of the results of replacing each element of
     * this stream with the contents of a mapped stream produced by applying
     * the provided mapping function to each element.
     *
     * @param callable $mapper A function to apply to each element which produces a stream of new values
     *
     * @return \Pipeline\Stream
     */
    public function flatMap(callable $mapper) : Stream;

    /**
     * Returns a stream consisting of the distinct elements.
     *
     * @return \Pipeline\Stream
     */
    public function distinct() : Stream;

    /**
     * Returns a stream consisting of the elements of this stream,
     * sorted according to the provided callable.
     *
     * @param callable $comparator Used to compare stream elements
     *
     * @return \Pipeline\Stream
     */
    public function sorted(callable $comparator = null) : Stream;

    /**
     * Returns a stream consisting of the elements of this stream, additionally
     * performing the provided action on each element as elements are consumed
     * from the resulting stream.
     *
     * <code>
     * <?php
     *     Pipeline::of("one", "two", "three", "four")
     *         ->filter(function ($e) { strlen($e) > 3})
     *         ->peek(function ($e) { echo "Filtered value: $e"})
     *         ->map('strtoupper')
     *         ->peek(function ($e) { echo "Mapped value: $e"})
     *         ->toArray();
     * </code>
     *
     * @param callable $action Action to perform on the elements as they are consumed from the stream
     *
     * @return \Pipeline\Stream
     */
    public function peek(callable $action) : Stream;

    /**
     * Returns a stream consisting of the elements of this stream, truncated
     * to be no longer than $maxSize in length.
     *
     * @param integer $maxSize The max number of elements the stream should be limited to
     *
     * @return \Pipeline\Stream
     */
    public function limit(int $maxSize) : Stream;

    /**
     * Returns a stream consisting of the remaining elements of this stream
     * after discarding the first $n elements of the stream.
     * If this stream contains fewer than {@code n} elements then an
     * empty stream will be returned.
     *
     * @param integer $n The number of leading elements to skip
     *
     * @return \Pipeline\Stream
     */
    public function skip(int $n) : Stream;

    /**
     * Performs an action for each element of this stream.
     *
     * <code>
     * <?php
     *  Pipeline::of("one", "two", "three", "four")
     *      ->forEach(function (string $e) {
     *          file_put_contents('file.log', $e, FILE_APPEND | LOCK_EX);
     *      });
     * </code>
     *
     * @param callable $action Action to perform on each element
     */
    public function forEach (callable $action);

    /**
     * Returns an array containing the elements of this stream.
     *
     * @return array Elements of this stream
     */
    public function toArray() : array;

    /**
     * Performs a reduction on elements of this stream,
     * Using the provided identity value and an accumulation function,
     * and returns the reduced value.
     *
     * <code>
     * <?php
     *  $sum = Pipeline::wrap(range(0, 100))
     *      ->reduce(0, function (int $state, int $item) {
     *          return $state + $item;
     *      });
     * </code>
     *
     * @param mixed    $state       The initial state value
     * @param callable $accumulator Callback function for combining two values
     *
     * @return mixed
     */
    public function reduce($state, callable $accumulator);

    /**
     * Performs a a operation on the elements of this stream using a Collector
     *
     * <code>
     * <?php
     * $peopleByCity = $stream
     *      ->collect(Collectors::groupingBy(function (Person $p) {
     *          return $p->getCityName();
     *      }));
     * </code>
     *
     * @param callable $collector The collector describing the reduction
     *
     * @see \Pipeline\Collectors
     */
    public function collect(Collector $collector);

    /**
     * Returns the minimum element of this stream according to the provided callable $comparator
     *
     * @param callable $comparator a function to compare elements of this stream
     *
     * @return mixed the minimum element of this stream
     */
    public function min(callable $comparator = null);

    /**
     * Returns the maximum element of this stream according to the provided callable $comparator
     *
     * @param callable $comparator a function to compare elements of this stream
     *
     * @return mixed The maximum element of this stream
     */
    public function max(callable $comparator = null);

    /**
     * Returns the count of elements in this stream.
     *
     * @return the count of elements in this stream
     */
    public function count() : int;

    /**
     * Returns whether any elements of this stream match the provided callable predicate.
     *
     * @param callable $predicate To apply to elements of this stream
     *
     * @return boolean
     */
    public function anyMatch(callable $predicate) : bool;

    /**
     * Returns whether all elements of this stream match the provided predicate.
     * May not evaluate the predicate on all elements if not necessary for determining the result.
     *
     * @param callable $predicate To apply to elements of this stream
     *
     * @return boolean
     */
    public function allMatch(callable $predicate) : bool;

    /**
     * Returns whether no elements of this stream match the provided predicate.
     * May not evaluate the predicate on all elements if not necessary for determining the result.
     *
     * @param callable $predicate To apply to elements of this stream
     *
     * @return boolean
     */
    public function noneMatch(callable $predicate) : bool;

    /**
     * Returns the first element of this stream that match the provided predicate,
     * Returns NULL If can't find a match.
     *
     * @return mixed
     */
    public function findFirst(callable $predicate = null);
}
