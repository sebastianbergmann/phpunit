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

class ChunkIterator extends \IteratorIterator
{
    protected int $chunk_idx = 0;

    protected int $chunk_num = 0;

    public function __construct(RecursiveIterator $iterator, array $chunk_conf)
    {
        parent::__construct($iterator);

				list($chunk_idx, $chunk_num) = $chunk_conf;

        // check chunk_num inputs
        if ($chunk_num < 0) {
            $chunk_num = 0;
        }

        // check chunk_idx inputs
        if ($chunk_idx < 0) {
            $chunk_idx = 0;
        }

        if ($chunk_idx > $chunk_num) {
            $chunk_idx = $chunk_num;
        }

        if ($chunk_num > 1 && $chunk_idx < 1) {
            $chunk_idx = 1;
        }

        $this->chunk_idx = $chunk_idx;
        $this->chunk_num = $chunk_num;
    }

    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        $test_idx   = (int) $this->getInnerIterator()->key();
        $test_count = iterator_count($this);

        $test_per_chunk = (int) ceil($test_count / $this->chunk_num);
        $idx_min        = (int) ($this->chunk_idx - 1) * $test_per_chunk;
        $idx_max        = $idx_min + $test_per_chunk;

        return ($test_idx >= $idx_min) && ($test_idx <= $idx_max);
    }
}
