<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use function ceil;
use IteratorIterator;
use PHPUnit\Framework\TestSuite;
use RecursiveIterator;
use FilterIterator;

final class ChunkFilterIterator extends FilterIterator
{
    protected int $chunkIndex = 0;

    protected int $chunkNumber = 0;

    public function __construct(RecursiveIterator $iterator, array $chunkConfs)
    {
        parent::__construct($iterator);

        [$chunkIndex, $chunkNumber] = $chunkConfs;

        // check chunkNumber inputs
        if ($chunkNumber < 0) {
            $chunkNumber = 0;
        }

        // check chunkIndex inputs
        if ($chunkIndex < 0) {
            $chunkIndex = 0;
        }

        if ($chunkIndex > $chunkNumber) {
            $chunkIndex = $chunkNumber;
        }

        if ($chunkNumber > 1 && $chunkIndex < 1) {
            $chunkIndex = 1;
        }

        $this->chunkIndex  = $chunkIndex;
        $this->chunkNumber = $chunkNumber;
    }

    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        $testIndex = (int) $this->getInnerIterator()->key();
        $testCount = iterator_count($this);

        $testPerChunk = (int) ceil($testCount / $this->chunkNumber);
        $idxMin       = (int) ($this->chunkIndex - 1) * $testPerChunk;
        $idxMax       = $idxMin + $testPerChunk;

        return ($testIndex >= $idxMin) && ($testIndex <= $idxMax);
    }
}
