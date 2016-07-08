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
 * A sequence of mixed elements supporting aggregate operations.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
interface MixedStream extends Stream
{
    /**
     * Returns an IntStream consisting of the results of applying the
     * given function to the elements of this stream.
     *
     * @param callable $mapper callable to apply to each element
     *
     * @return \Pipeline\IntStream
     */
    public function mapToInt(callable $mapper) : IntStream;

    /**
     * Returns an FloatStream consisting of the results of applying the
     * given function to the elements of this stream.
     *
     * @param callable $mapper callable to apply to each element
     *
     * @return \Pipeline\FloatStream
     */
    public function mapToFloat(callable $mapper) : FloatStream;

    /**
     * Returns an IntStream consisting of the results of replacing each
     * element of this stream with the contents of a mapped stream produced by
     * applying the provided mapping function to each element.
     *
     * @param callable $mapper A function to apply to each element which produces a stream of new values
     *
     * @return \Pipeline\IntStream
     */
    public function flatMapToInt(callable $mapper) : IntStream;

    /**
     * Returns an FloatStream consisting of the results of replacing each
     * element of this stream with the contents of a mapped stream produced by
     * applying the provided mapping function to each element.
     *
     * @param callable $mapper A function to apply to each element which produces a stream of new values
     *
     * @return \Pipeline\FloatStream
     */
    public function flatMapToFloat(callable $mapper) : FloatStream;
}
