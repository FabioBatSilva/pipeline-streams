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

use ArrayObject;

use Pipeline\Op\ForEachOp;
use Pipeline\Op\CollectOp;
use Pipeline\Op\ReduceOp;
use Pipeline\Op\MatchOp;
use Pipeline\Op\FindOp;

use Pipeline\Sink\SortingSink;
use Pipeline\Sink\MappingSink;
use Pipeline\Sink\SlicingSink;
use Pipeline\Sink\InvokingSink;
use Pipeline\Sink\FilteringSink;
use Pipeline\Sink\FlatMappingSink;
use Pipeline\Sink\ChainedReference;

/**
 * Implements a pipeline stage or pipeline source stage implementing whose elements are numeric.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class NumericPipeline extends Pipeline implements NumericStream
{
    /**
     * {@inheritdoc}
     */
    public function average()
    {
        return $this->collect(Collectors::averagingNumbers());
    }

    /**
     * {@inheritdoc}
     */
    public function sum()
    {
        return $this->collect(Collectors::summingNumbers());
    }
}
