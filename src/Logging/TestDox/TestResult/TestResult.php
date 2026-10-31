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

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Test\PropertyFalsified;
use PHPUnit\Framework\TestStatus\TestStatus;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestResult
{
    private TestMethod $test;
    private TestStatus $status;
    private ?Throwable $throwable;
    private ?PropertyFalsified $propertyFalsified;

    public function __construct(TestMethod $test, TestStatus $status, ?Throwable $throwable, ?PropertyFalsified $propertyFalsified = null)
    {
        $this->test              = $test;
        $this->status            = $status;
        $this->throwable         = $throwable;
        $this->propertyFalsified = $propertyFalsified;
    }

    public function test(): TestMethod
    {
        return $this->test;
    }

    public function status(): TestStatus
    {
        return $this->status;
    }

    /**
     * @phpstan-assert-if-true !null $this->throwable
     */
    public function hasThrowable(): bool
    {
        return $this->throwable !== null;
    }

    public function throwable(): ?Throwable
    {
        return $this->throwable;
    }

    /**
     * Returns the event that describes how the test, a property, was
     * falsified, and null when the test is not a property that was falsified.
     */
    public function propertyFalsified(): ?PropertyFalsified
    {
        return $this->propertyFalsified;
    }
}
