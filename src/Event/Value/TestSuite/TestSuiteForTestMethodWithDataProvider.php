<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\Code\TestCollection;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuiteForTestMethodWithDataProvider extends TestSuite
{
    /**
     * @psalm-var class-string
     */
    private string $className;
    private string $methodName;
    private string $file;
    private int $line;

    /**
     * @psalm-param class-string $name
     */
    public function __construct(string $name, int $size, array $groups, array $provides, array $requires, string $sortId, TestCollection $tests, array $warnings, string $className, string $methodName, string $file, int $line)
    {
        parent::__construct($name, $size, $groups, $provides, $requires, $sortId, $tests, $warnings);

        $this->className  = $className;
        $this->methodName = $methodName;
        $this->file       = $file;
        $this->line       = $line;
    }

    /**
     * @psalm-return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    public function file(): string
    {
        return $this->file;
    }

    public function line(): int
    {
        return $this->line;
    }

    /**
     * @psalm-assert-if-true TestSuiteForTestMethodWithDataProvider $this
     */
    public function isForTestMethodWithDataProvider(): bool
    {
        return true;
    }
}
