<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use function count;
use Countable;
use IteratorAggregate;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class TestSuiteCollection implements Countable, IteratorAggregate
{
    /**
     * @var TestSuite[]
     */
    private $testSuites;

    /**
     * @param TestSuite[] $testSuites
     */
    public static function fromArray(array $testSuites): self
    {
        return new self(...$testSuites);
    }

    private function __construct(TestSuite ...$testSuites)
    {
        $this->testSuites = $testSuites;
    }

    /**
     * @return TestSuite[]
     */
    public function asArray(): array
    {
        return $this->testSuites;
    }

    public function count(): int
    {
        return count($this->testSuites);
    }

    public function getIterator(): TestSuiteCollectionIterator
    {
        return new TestSuiteCollectionIterator($this);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
