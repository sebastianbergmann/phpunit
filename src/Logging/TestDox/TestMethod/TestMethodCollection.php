<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use IteratorAggregate;

/**
 * @psalm-immutable
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestMethodCollection implements IteratorAggregate
{
    /**
     * @psalm-var list<TestMethod>
     */
    private readonly array $testMethods;

    /**
     * @psalm-param list<TestMethod> $testMethods
     */
    public static function fromArray(array $testMethods): self
    {
        return new self(...$testMethods);
    }

    private function __construct(TestMethod ...$testMethods)
    {
        $this->testMethods = $testMethods;
    }

    /**
     * @psalm-return list<TestMethod>
     */
    public function asArray(): array
    {
        return $this->testMethods;
    }

    public function getIterator(): TestMethodCollectionIterator
    {
        return new TestMethodCollectionIterator($this);
    }
}
