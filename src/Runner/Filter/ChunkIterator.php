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

use InvalidArgumentException;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\WarningTestCase;
use PHPUnit\Util\RegularExpression;
use RecursiveFilterIterator;
use RecursiveIterator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ChunkIterator extends RecursiveFilterIterator
{
    /**
     * @var int
     */
    private $chunk;

    /**
     * @var int
     */
    private $numChunks;

    /**
     * @var Test[][]
     */
    private $chunks;

    /**
     * @throws \Exception
     */
    public function __construct(RecursiveIterator $iterator, array $args)
    {
        parent::__construct($iterator);

        $this->chunk = $args['chunk'];
        $this->numChunks = $args['numChunks'];

        if ($this->chunk > $this->numChunks) {
            throw new InvalidArgumentException(sprintf('You only configured %s chunks but passed chunk %s',
                $this->numChunks,
                $this->chunk
            ));
        }

        $tests = iterator_to_array($iterator);
        $numTests = count($tests);
        $numTestsPerChunk = (int) round($numTests / $this->numChunks);

        $this->chunks = array_chunk(iterator_to_array($iterator), $numTestsPerChunk);
    }

    public function accept(): bool
    {
        $chunkKey = $this->chunk - 1;

        if (!isset($this->chunks[$chunkKey])) {
            return false;
        }

        $chunk = $this->chunks[$chunkKey];

        $test = $this->getInnerIterator()->current();

        return in_array($test, $chunk, true);
    }

}
