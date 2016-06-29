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
 * Flags corresponding to characteristics of streams and operations.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
interface PipelineOpFlag
{
    /**
     * The bit value to set or inject DISTINCT flag.
     */
    const IS_DISTINCT = 1;

    /**
     * The bit value to clear DISTINCT flag.
     */
    const NOT_DISTINCT = 2;

    /**
     * The bit value to set or inject SORTED flag.
     */
    const IS_SORTED = 4;

    /**
     * The bit value to clear SORTED flag.
     */
    const NOT_SORTED = 8;

    /**
     * The bit value to set or inject ORDERED flag.
     */
    const IS_ORDERED = 16;

    /**
     * The bit value to clear ORDERED flag.
     */
    const NOT_ORDERED = 32;

    /**
     * The bit value to set SIZED flag.
     */
    const IS_SIZED = 64;

    /**
     * The bit value to clear SIZED flag.
     */
    const NOT_SIZED = 128;

    /**
     * The bit value to inject SHORT_CIRCUIT flag.
     */
    const IS_SHORT_CIRCUIT = 256;
}
