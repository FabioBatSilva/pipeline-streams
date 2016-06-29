<?php

declare(strict_types=1);

namespace Pipeline;

/**
 * A sequence of elements supporting sequential operations.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
interface Stream
{
    /**
     * Returns a stream consisting of the elements of this stream that match the given predicate.
     *
     * @param callback $predicate Predicate to apply to each element to determine if it should be included
     *
     * @return \Pipeline\Stream
     */
    public function filter(callback $predicate) : Stream;

    /**
     * Returns a stream consisting of the results of applying the
     * given function to the elements of this stream.
     *
     * @param callback $mapper callback to apply to each element
     *
     * @return \Pipeline\Stream
     */
    public function map(callback $mapper) : Stream;

    /**
     * Returns an IntStream consisting of the results of applying the
     * given function to the elements of this stream.
     *
     * @param callback $mapper callback to apply to each element
     *
     * @return \Pipeline\Stream
     */
    public function mapToInt(callback $mapper) : IntStream;

    /**
     * Returns a stream consisting of the results of replacing each element of
     * this stream with the contents of a mapped stream produced by applying
     * the provided mapping function to each element.
     *
     * @param callback $mapper A function to apply to each element which produces a stream of new values
     *
     * @return \Pipeline\Stream
     */
    public function flatMap(callback $mapper) : Stream;

    /**
     * Returns an IntStream consisting of the results of replacing each
     * element of this stream with the contents of a mapped stream produced by
     * applying the provided mapping function to each element.
     *
     * @param callback $mapper A function to apply to each element which produces a stream of new values
     *
     * @return \Pipeline\IntStream
     */
    public function flatMapToInt(callback $mapper) : IntStream;

    /**
     * Returns a stream consisting of the distinct elements.
     *
     * @return \Pipeline\Stream
     */
    public function distinct() : Stream;

    /**
     * Returns a stream consisting of the elements of this stream,
     * sorted according to the provided callback.
     *
     * @param callback $comparator Used to compare stream elements
     *
     * @return \Pipeline\Stream
     */
    public function sorted(callback $comparator) : Stream;

    /**
     * Returns a stream consisting of the elements of this stream, additionally
     * performing the provided action on each element as elements are consumed
     * from the resulting stream.
     *
     * <code>
     * <?php
     *     Streams.of("one", "two", "three", "four")
     *         ->filter(function ($e) { strlen($e) > 3})
     *         ->peek(function ($e) { echo "Filtered value: $e"})
     *         ->map('strtoupper')
     *         ->peek(function ($e) { echo "Mapped value: $e"})
     *         ->toArray();
     * </code>
     *
     * @param callback $action Action to perform on the elements as they are consumed from the stream
     *
     * @return \Pipeline\Stream
     */
    public function peek(callback $action) : Stream;

    /**
     * Returns a stream consisting of the elements of this stream, truncated
     * to be no longer than $maxSize in length.
     *
     * @param integer $maxSize The max number of elements the stream should be limited to
     *
     * @return \Pipeline\Stream
     */
    public function limit(integer $maxSize) : Stream;

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
    public function skip(integer $n) : Stream;

    /**
     * Performs an action for each element of this stream.
     *
     * <code>
     * <?php
     *  Streams.of("one", "two", "three", "four")
     *      ->forEach(function (string $e) {
     *          file_put_contents('file.log', $e, FILE_APPEND | LOCK_EX);
     *      });
     * </code>
     *
     * @param callback $action Action to perform on each element
     */
    public function forEach(callback $action);

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
     *  $sum = Streams.of(range(0, 100))
     *      ->reduce(function (integer $identity, integer $item) {
     *          return $identity + $item;
     *      }, 0);
     * </code>
     *
     * @param callback $accumulator Callback function for combining two values
     * @param mixed    $identity    The initial value for the accumulating function
     *
     * @return mixed
     */
    public function reduce(callback $accumulator, $identity = null) : mixed;

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
     * @param callback $collector The collector describing the reduction
     *
     * @see \Pipeline\Collectors
     */
    public function collect(callback $collector) : mixed;

    /**
     * Returns the minimum element of this stream according to the provided callback $comparator
     *
     * @param callback $comparator a function to compare elements of this stream
     *
     * @return mixed the minimum element of this stream
     */
    public function min(callback $comparator) : mixed;

    /**
     * Returns the maximum element of this stream according to the provided callback $comparator
     *
     * @param callback $comparator a function to compare elements of this stream
     *
     * @return mixed The maximum element of this stream
     */
    public function max(callback $comparator) : mixed;

    /**
     * Returns the count of elements in this stream.
     *
     * @return the count of elements in this stream
     */
    public function count() : integer;

    /**
     * Returns whether any elements of this stream match the provided callback predicate.
     *
     * @param callback $predicate To apply to elements of this stream
     *
     * @return boolean
     */
    public function anyMatch(callback $predicate) : boolean;

    /**
     * Returns whether all elements of this stream match the provided predicate.
     * May not evaluate the predicate on all elements if not necessary for determining the result.
     *
     * @param callback $predicate To apply to elements of this stream
     *
     * @return boolean
     */
    public function allMatch(callback $predicate) : boolean;

    /**
     * Returns whether no elements of this stream match the provided predicate.
     * May not evaluate the predicate on all elements if not necessary for determining the result.
     *
     * @param callback $predicate To apply to elements of this stream
     *
     * @return boolean
     */
    public function noneMatch(callback $predicate) : boolean;

    /**
     * Returns the first element of this stream, or NULL If the stream is empty.
     *
     * @return boolean
     */
    public function findFirst() : mixed;
}
