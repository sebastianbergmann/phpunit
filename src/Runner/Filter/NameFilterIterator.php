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

use function end;
use function preg_match;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use RecursiveFilterIterator;
use RecursiveIterator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class NameFilterIterator extends RecursiveFilterIterator
{
    /**
     * @psalm-var non-empty-string
     */
    private readonly string $regularExpression;

    /**
     * @psalm-var ?int
     */
    private readonly ?int $dataSetMinimum;

    /**
     * @psalm-var ?int
     */
    private readonly ?int $dataSetMaximum;

    /**
     * @psalm-param RecursiveIterator<int, Test> $iterator
     * @psalm-param non-empty-string $filter
     */
    public function __construct(RecursiveIterator $iterator, string $filter)
    {
        parent::__construct($iterator);

        $preparedFilter = $this->prepareFilter($filter);

        $this->regularExpression = $preparedFilter['regularExpression'];
        $this->dataSetMinimum    = $preparedFilter['dataSetMinimum'];
        $this->dataSetMaximum    = $preparedFilter['dataSetMaximum'];
    }

    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        if (!$test instanceof TestCase) {
            return false;
        }

        $name = $test::class . '::' . $test->nameWithDataSet();

        $accepted = @preg_match($this->regularExpression, $name, $matches) === 1;

        if ($accepted && isset($this->dataSetMaximum)) {
            $set      = end($matches);
            $accepted = $set >= $this->dataSetMinimum && $set <= $this->dataSetMaximum;
        }

        return $accepted;
    }

    /**
     * @psalm-param non-empty-string $filter
     *
     * @psalm-return array{regularExpression: non-empty-string, dataSetMinimum: ?int, dataSetMaximum: ?int}
     */
    abstract protected function prepareFilter(string $filter): array;
}
