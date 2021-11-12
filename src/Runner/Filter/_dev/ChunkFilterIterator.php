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
use FilterIterator;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Util\DevTool;
use RecursiveIterator;

final class ChunkFilterIterator extends FilterIterator
{
    protected int $chunkIndex = 0;

    protected int $chunkNumber = 0;

    protected int $numTests = 0;

    protected TestSuite $suite;

    public function __construct(RecursiveIterator $iterator, array $chunkConfs)
    {
        parent::__construct($iterator);

        [$chunkIndex, $chunkNumber, $suite] = $chunkConfs;

        $this->suite = $suite;

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
        return true;

        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        if ($this->chunkNumber < 2) {
            return true;
        }

        $testIndex = (int) $this->getInnerIterator()->key();
        $cur       = $this->getInnerIterator()->current();
        //				DevTool::print_rdie(\ReflectionClass::getProperties($cur));
        $this->numTests = 0;

        if (null !== $this->suite) {
            //$tests= $this->suite->tests();
            //$this->numTests= count($tests);

            $iterator       = $this->suite->getIterator();
            $this->numTests = iterator_count($this->suite);
            //$this->numTests= iterator_count($this->getInnerIterator());
        }
        $str = "test count = {$this->numTests} index={$testIndex} \n\n";
        //				DevTool::print_rdie($str);

        if ($this->chunkNumber > $this->numTests) {
            return true;
        }

        $testIndex = (int) $this->getInnerIterator()->key();

        $testPerChunk = (int) ceil($this->numTests / $this->chunkNumber);
        $idxMin       = (int) ($this->chunkIndex - 1) * $testPerChunk;
        $idxMax       = $idxMin + $testPerChunk;

        //return true;
        return ($testIndex >= $idxMin) && ($testIndex <= $idxMax);
    }
}
