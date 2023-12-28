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
use function implode;
use function preg_match;
use Exception;
use PHPUnit\Framework\SelfDescribing;
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
    protected ?string $filter = null;
    protected ?int $filterMin = null;
    protected ?int $filterMax = null;

    /**
     * @psalm-param RecursiveIterator<int, Test> $iterator
     * @psalm-param non-empty-string $filter
     *
     * @throws Exception
     */
    public function __construct(RecursiveIterator $iterator, string $filter)
    {
        parent::__construct($iterator);
        $this->setFilter($filter);
    }

    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        $tmp = $this->describe($test);

        if ($tmp[0] !== '') {
            $name = implode('::', $tmp);
        } else {
            $name = $tmp[1];
        }

        if (null === $this->filter) {
            return false;
        }

        $accepted = @preg_match($this->filter, $name, $matches) === 1;

        if ($accepted && isset($this->filterMax)) {
            $set      = end($matches);
            $accepted = $set >= $this->filterMin && $set <= $this->filterMax;
        }

        return $accepted;
    }

    abstract protected function setFilter(string $filter): void;

    /**
     * @psalm-return array{0: string, 1: string}
     */
    private function describe(Test $test): array
    {
        if ($test instanceof TestCase) {
            return [$test::class, $test->nameWithDataSet()];
        }

        if ($test instanceof SelfDescribing) {
            return ['', $test->toString()];
        }

        return ['', $test::class];
    }
}
