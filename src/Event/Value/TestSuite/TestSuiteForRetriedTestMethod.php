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
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestSuiteForRetriedTestMethod extends TestSuite
{
    /**
     * @var class-string
     */
    private string $className;

    /**
     * @var non-empty-string
     */
    private string $methodName;
    private string $file;
    private int $line;
    private bool $isForDataSet;

    /**
     * @var positive-int
     */
    private int $maxAttempts;

    /**
     * @param non-empty-string $name
     * @param class-string     $className
     * @param non-empty-string $methodName
     * @param positive-int     $maxAttempts
     */
    public function __construct(string $name, int $size, TestCollection $tests, string $className, string $methodName, string $file, int $line, bool $isForDataSet, int $maxAttempts)
    {
        parent::__construct($name, $size, $tests);

        $this->className    = $className;
        $this->methodName   = $methodName;
        $this->file         = $file;
        $this->line         = $line;
        $this->isForDataSet = $isForDataSet;
        $this->maxAttempts  = $maxAttempts;
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * @return non-empty-string
     */
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
     * Returns true when this test suite holds the attempts for a single data set
     * of a test method that uses a data provider.
     */
    public function isForDataSet(): bool
    {
        return $this->isForDataSet;
    }

    /**
     * @return positive-int
     */
    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function isForRetriedTestMethod(): true
    {
        return true;
    }
}
