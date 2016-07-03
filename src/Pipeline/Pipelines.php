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

use Iterator;
use ArrayIterator;

/**
 * Utility methods for operating on and creating streams.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class Pipelines
{
    /**
     * Construct
     */
    private function __construct() { }

    /**
     * Returns a Stream containing the parameters as elements
     *
     * @param mixed $values,...
     *
     * @return \Pipeline\Stream
     */
    public static function of(...$values)
    {
        $iterator = new ArrayIterator($values);
        $pipeline = Pipeline::head($iterator);

        return $pipeline;
    }

    /**
     * Wrap the input values in a Stream object.
     *
     * @param array|\Iterator $source
     *
     * @return \Pipeline\Stream
     *
     * @throws \InvalidArgumentException if the $source arg is not valid.
     */
    public static function wrap($source)
    {
        if ($source instanceof Iterator) {
            return Pipeline::head($source);
        }

        if (is_array($source)) {
            return Pipeline::head(new ArrayIterator($source));
        }

        throw new \InvalidArgumentException(sprintf(
            'Argument 1 passed to %s($source) must be an instance of array|\Iterator, %s given.',
            __METHOD__,
            is_object($source) ? get_class($source) : gettype($source)
        ));
    }

}
