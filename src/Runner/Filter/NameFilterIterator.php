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
 * @extends RecursiveFilterIterator<int, Test, RecursiveIterator<int, Test>>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class NameFilterIterator extends RecursiveFilterIterator
{
    private readonly CompiledNameFilter $filter;

    /**
     * @param RecursiveIterator<int, Test> $iterator
     * @param non-empty-string             $filter
     */
    public function __construct(RecursiveIterator $iterator, string $filter)
    {
        parent::__construct($iterator);

        $this->filter = CompiledNameFilter::from($filter);
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

        $accepted = @preg_match($this->filter->regularExpression(), $name, $matches) === 1;

        if ($accepted && $this->filter->hasDataSetRange()) {
            $set      = end($matches);
            $accepted = $set >= $this->filter->dataSetMinimum() && $set <= $this->filter->dataSetMaximum();
        }

        return $this->doAccept($accepted);
    }

    abstract protected function doAccept(bool $result): bool;
}
