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
 * Used to conduct values through the stages of a stream pipeline,
 * with additional methods to manage size information, control flow, etc.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
interface Sink
{
    /**
     * Resets the sink state to receive a fresh data set.
     * This must be called before sending any data to the sink.
     * After calling end, you may call this method to reset the sink for another calculation.
     */
    public function begin();

    /**
     * Performs this operation on the given argument.
     *
     * @param mixed $item The input argument
     */
    public function accept($item);

    /**
     * Indicates that all elements have been pushed.
     * If the Sink is stateful, it should send any stored state downstream at this time,
     * and should clear any accumulated state (and associated resources).
     */
    public function end();

    /**
     * Indicates that this Sink does not wish to receive any more data.
     *
     * @return boolean TRUE if cancellation is requested
     */
    public function cancellationRequested() : bool;
}
